<?php

namespace App\HttpController\Api\Admin\Merchant;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Merchant\ChannelCostStatisticModel;
use App\Model\Merchant\ChannelCpaConfigModel;
use App\Model\Merchant\ChannelDownloadModel;
use App\Model\Merchant\ChannelInstallModel;
use App\Model\Merchant\ChannelInstallStatisticModel;
use App\Model\Merchant\ChannelModel;
use App\Model\Merchant\MerchantModel;
use App\Model\Navigation\AdClickStatisticModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageStatisticModel;
use App\Model\User\UserVipOrderModel;
use App\Service\Merchant\ChannelService;
use App\Utility\Func;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * Class Channel
 * @package App\HttpController\Api\Admin\Merchant
 * @ApiGroup(groupName="后台-商户-渠道 Admin/Merchant/Channel")
 * @ApiGroupDescription("后台商户渠道相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Channel extends AdminBase
{
    /**
     * 渠道列表
     * @Api(name="渠道列表",path="/Api/Admin/Merchant/Channel/channelList")
     * @ApiDescription("渠道列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelId", alias="渠道id", type="int", optional="", min="1", description="渠道id")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="channelDomain", alias="渠道域名", type="string", optional="", description="渠道域名")
     * @Param(name="merchantId", alias="商户id", type="int", optional="", min="1", description="商户id")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"channelId":1,"merchantId":1,"channelKey":"test123","channelDomain":"","percentage":100,"cost":"0.00","remark":"这是备注","status":1,"merchantName":"测试商户"}],"options":{"channelId":"1"}},"systemTimestamp":1699873329,"systemDateTime":"2023-11-13 19:02:09","msg":"OK"})
     */
    public function channelList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['merchantId']) && $keyword['merchantId'] = $param['merchantId'];
            isset($param['channelId']) && $keyword['channelId'] = $param['channelId'];
            isset($param['channelKey']) && $keyword['channelKey'] = trim($param['channelKey']);
            isset($param['channelDomain']) && $keyword['channelDomain'] = trim($param['channelDomain']);
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            // 2023-11-13 增加商户名的筛选字段
            if (isset($param['merchantName'])) {
                $merchant = MerchantModel::create()
                    ->where([
                        'merchantName' => $param['merchantName'],
                        'status' => [MerchantModel::STATE_DELETED, '!='],
                    ])
                    ->get();
                if ($merchant) {
                    $keyword['merchantId'] = $merchant->merchantId;
                }
            }

            $field = [
                'channelId',
                'merchantId',
                'channelKey',
                'channelDomain',
                'percentage',
                'cost',
                'cpaCost',
                'coefficient',
                'remark',
                'status',
            ];

            $data = ChannelModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道详情
     * @Api(name="渠道详情",path="/Api/Admin/Merchant/Channel/channelDetail")
     * @ApiDescription("渠道详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelId", alias="渠道id", type="int", required="", min="1", description="渠道id")
     * @ApiSuccess({"code":200,"result":{"channelId":1,"merchantId":1,"channelKey":"index.html","channelDomain":"","percentage":100,"cost":"0.00","remark":"这是备注","status":1,"createTime":"2023-08-23 15:08:00","updateTime":"2023-11-13 22:02:40"},"systemTimestamp":1699938393,"systemDateTime":"2023-11-14 13:06:33","msg":"OK"})
     */
    public function channelDetail()
    {
        $param = $this->request()->getRequestParam();

        try {

            $data = ChannelModel::create()->get($param['channelId']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道添加
     * @Api(name="渠道添加",path="/Api/Admin/Merchant/Channel/add")
     * @ApiDescription("渠道添加")
     * @Method(allow=["POST"])
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="1", description="商户id")
     * @Param(name="channelKey", alias="渠道key", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="channelDomain", alias="渠道域名", type="string", required="", description="渠道域名")
     * @Param(name="percentage", alias="计量百分比", type="int", required="", min="0", max="100", description="计量百分比 0-100")
     * @Param(name="cost", alias="渠道单次安装价格", type="float", required="", min="0", description="渠道单次安装价格")
     * @Param(name="remark", alias="备注", type="string", required="", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'merchantId' => $param['merchantId'],
                'channelKey' => trim($param['channelKey']),
                'channelDomain' => trim($param['channelDomain']),
                'percentage' => intval($param['percentage']),
                'cost' => floatval($param['cost']),
                'cpaCost' => floatval($param['cpaCost']),
                'coefficient' => floatval($param['coefficient']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
            ];

            $result = ChannelService::getInstance()->addChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_ADD,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 渠道编辑
     * @Api(name="渠道编辑",path="/Api/Admin/Merchant/Channel/edit")
     * @ApiDescription("渠道编辑")
     * @Method(allow=["POST"])
     * @Param(name="channelId", alias="渠道id", type="int", required="", min="1", description="渠道id")
     * @Param(name="channelKey", alias="渠道key", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="channelDomain", alias="渠道域名", type="string", required="", description="渠道域名")
     * @Param(name="percentage", alias="计量百分比", type="int", required="", min="0", max="100", description="计量百分比 0-100")
     * @Param(name="cost", alias="渠道单次安装价格", type="float", required="", min="0", description="渠道单次安装价格")
     * @Param(name="remark", alias="备注", type="string", required="", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'channelId' => $param['channelId'],
                'channelKey' => trim($param['channelKey']),
                'channelDomain' => trim($param['channelDomain']),
                'percentage' => intval($param['percentage']),
                'cost' => floatval($param['cost']),
                'cpaCost' => floatval($param['cpaCost']),
                'coefficient' => floatval($param['coefficient']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
            ];

            $result = ChannelService::getInstance()->editChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 渠道修改状态
     * @Api(name="渠道修改状态",path="/Api/Admin/Merchant/Channel/setStatus")
     * @ApiDescription("渠道修改状态")
     * @Method(allow=["POST"])
     * @Param(name="channelId", alias="渠道id", type="int", required="", min="1", description="渠道id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'channelId' => $param['channelId'],
                'status' => intval($param['status']),
            ];

            $result = ChannelService::getInstance()->editChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 渠道删除
     * @Api(name="渠道删除",path="/Api/Admin/Merchant/Channel/delete")
     * @ApiDescription("渠道删除")
     * @Method(allow=["POST"])
     * @Param(name="channelId", alias="渠道id", type="int", required="", min="1", description="渠道id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            $data['channelId'] = $param['channelId'];
            $data['status'] = ChannelModel::STATE_DELETED;

            $result = ChannelService::getInstance()->editChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_DELETE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 渠道总计列表（管理员）
     * @Api(name="渠道总计列表（管理员）",path="/Api/Admin/Merchant/Channel/statisticTotalListSystem")
     * @ApiDescription("渠道总计列表（管理员）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["date_DESC", "date_ASC", "realCount_DESC", "realCount_ASC", "realActive_DESC", "realActive_ASC"], description="1.时间倒序（date_DESC）2.时间正序（date_ASC）3.真实安装倒叙（realCount_DESC）4.真实安装正序（realCount_ASC）5.真实日活跃倒序（realActive_DESC）6.真实日活跃正序（realActive_ASC）")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"date":"2023-09-21","realInstallAndroid":"889","realActiveAndroid":"938","realInstallIOS":"0","realActiveIOS":"0","realInstallIOSBookmark":"0","realActiveIOSBookmark":"0","realInstallTotal":"889","realActiveTotal":"938","realRetainedUserTotal":49},{"date":"2023-09-20","realInstallAndroid":"678","realActiveAndroid":"681","realInstallIOS":"0","realActiveIOS":"0","realInstallIOSBookmark":"0","realActiveIOSBookmark":"0","realInstallTotal":"678","realActiveTotal":"681","realRetainedUserTotal":3}],"options":{"dateStart":"2023-09-20","dateEnd":"2023-09-21"},"sum":{"installAndroid":"935","realInstallAndroid":"1567","activeAndroid":"968","realActiveAndroid":"1619","installIOS":"0","realInstallIOS":"0","activeIOS":"0","realActiveIOS":"0","installIOSBookmark":"0","realInstallIOSBookmark":"0","activeIOSBookmark":"0","realActiveIOSBookmark":"0","installTotal":"935","realInstallTotal":"1567","activeTotal":"968","realActiveTotal":"1619","realRetainedUserTotal":52}},"systemTimestamp":1695794433,"systemDateTime":"2023-09-27 14:00:33","msg":"OK"})
     */
    public function statisticTotalListSystem()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            if (isset($param['channelKey'])) {
                $channel = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->get();
                if ($channel) {
                    $keyword['channelId'] = $channel->channelId;
                } else {
                    // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                    return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
                }
            }
            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date',
                'IFNULL(SUM(realInstallAndroid), 0) AS realInstallAndroid',
                'IFNULL(SUM(realActiveAndroid), 0) AS realActiveAndroid',

                'IFNULL(SUM(realInstallIOS), 0) AS realInstallIOS',
                'IFNULL(SUM(realActiveIOS), 0) AS realActiveIOS',

                'IFNULL(SUM(realInstallIOSBookmark), 0) AS realInstallIOSBookmark',
                'IFNULL(SUM(realActiveIOSBookmark), 0) AS realActiveIOSBookmark',

                'IFNULL(SUM(realInstallTotal), 0) AS realInstallTotal',
                'IFNULL(SUM(realActiveTotal), 0) AS realActiveTotal',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ChannelInstallStatisticModel::create()
                ->setOrderType($sortType)
                ->group('date')
                ->getAll($page, $keyword, $pageSize, $field);

            foreach ($data['list'] as $datum) {
                $datum['realRetainedUserTotal'] = $datum['realActiveTotal'] - $datum['realInstallTotal']; // 真实留存
            }

            $data['sum'] = ChannelInstallStatisticModel::create()->getSum($keyword);
            $data['sum']['realRetainedUserTotal'] = $data['sum']['realActiveTotal'] - $data['sum']['realInstallTotal'];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道总计图表数据（管理员）
     * @Api(name="渠道总计图表数据（管理员）",path="/Api/Admin/Merchant/Channel/statisticTotalChartSystem")
     * @ApiDescription("渠道总计图表数据（管理员）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @ApiSuccess({"code":200,"result":[{"source":"IOSBookmark","createTimeBucketHour":18,"count":7},{"source":"IOSBookmark","createTimeBucketHour":19,"count":48},{"source":"IOSBookmark","createTimeBucketHour":20,"count":58},{"source":"IOSBookmark","createTimeBucketHour":21,"count":53},{"source":"IOSBookmark","createTimeBucketHour":22,"count":78},{"source":"IOSBookmark","createTimeBucketHour":23,"count":32},{"source":"Android","createTimeBucketHour":12,"count":1},{"source":"Android","createTimeBucketHour":16,"count":1},{"source":"Android","createTimeBucketHour":17,"count":1},{"source":"Android","createTimeBucketHour":18,"count":15},{"source":"Android","createTimeBucketHour":19,"count":59},{"source":"Android","createTimeBucketHour":20,"count":66},{"source":"Android","createTimeBucketHour":21,"count":88},{"source":"Android","createTimeBucketHour":22,"count":101},{"source":"Android","createTimeBucketHour":23,"count":70}],"systemTimestamp":1695799440,"systemDateTime":"2023-09-27 15:24:00","msg":"OK"})
     */
    public function statisticTotalChartSystem()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            if (isset($param['channelKey'])) {
                $channel = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->get();
                if ($channel) {
                    $keyword['channelId'] = $channel->channelId;
                } else {
                    // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                    return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
                }
            }
            isset($param['dateStart']) && $keyword['createDateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['createDateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $ci = ChannelInstallModel::create();
            $data = $ci
                ->field([
                    'source', 'createTimeBucketHour', 'count(channelInstallId) AS count'
                ])
                ->where($ci->parseKeywordToWhere($keyword))
                ->group('source,createTimeBucketHour')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道推广统计列表（管理员）
     * @Api(name="渠道推广统计列表（管理员）",path="/Api/Admin/Merchant/Channel/statisticListSystem")
     * @ApiDescription("渠道推广统计列表（管理员）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", description="1.时间倒序（date_DESC）2.时间正序（date_ASC）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-16","channelId":18,"installAndroid":1,"realInstallAndroid":1,"activeAndroid":3,"realActiveAndroid":3,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":1,"realInstallTotal":1,"activeTotal":3,"realActiveTotal":3,"channelKey":"test.html","merchantId":1,"merchantName":"测试商户","pageId":2,"retainedUserTotal":2,"realRetainedUserTotal":2,"clickCount":"9","h5ClickCount":"7","appClickCount":"2","retainedClickCount":"2","newAppClickCount":"0","installNewAppClickRatio":"0.00"},{"date":"2023-11-16","channelId":1,"installAndroid":3,"realInstallAndroid":6,"activeAndroid":10,"realActiveAndroid":13,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":3,"realInstallTotal":6,"activeTotal":10,"realActiveTotal":13,"channelKey":"index.html","merchantId":1,"merchantName":"测试商户","pageId":1,"retainedUserTotal":7,"realRetainedUserTotal":7,"clickCount":"8","h5ClickCount":"4","appClickCount":"4","retainedClickCount":"4","newAppClickCount":"0","installNewAppClickRatio":"0.00"},{"date":"2023-11-15","channelId":1,"installAndroid":3,"realInstallAndroid":3,"activeAndroid":5,"realActiveAndroid":5,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":3,"realInstallTotal":3,"activeTotal":5,"realActiveTotal":5,"channelKey":"index.html","merchantId":1,"merchantName":"测试商户","pageId":1,"retainedUserTotal":2,"realRetainedUserTotal":2,"clickCount":"4","h5ClickCount":"1","appClickCount":"3","retainedClickCount":"3","newAppClickCount":"0","installNewAppClickRatio":"0.00"}],"options":{"dateStart":"2023-11-01","dateEnd":"2023-11-28"},"sum":{"installAndroid":"7","realInstallAndroid":"10","activeAndroid":"18","realActiveAndroid":"21","installIOS":"0","realInstallIOS":"0","activeIOS":"0","realActiveIOS":"0","installIOSBookmark":"0","realInstallIOSBookmark":"0","activeIOSBookmark":"0","realActiveIOSBookmark":"0","installTotal":"7","realInstallTotal":"10","activeTotal":"18","realActiveTotal":"21","realRetainedUserTotal":11,"clickCount":"21","h5ClickCount":"12","appClickCount":"9","retainedClickCount":"9","newAppClickCount":"0","installNewAppClickRatio":"0.00"}},"systemTimestamp":1701179336,"systemDateTime":"2023-11-28 21:48:56","msg":"OK"})
     */
    public function statisticListSystem()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $clickKeyword = [];
            $psKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            if(isset($param['dateStart']) xor isset($param['dateEnd'])){
                return $this->writeJson(404, [], "日期选择错误");
            }
            if(strtotime($param['dateEnd'])<=strtotime($param['dateStart'])){
                return $this->writeJson(404, [], "日期选择错误");
            }
            if(!isset($param['dateStart'])){
                $param['dateStart']=date("Y-m-d");
            }
            if(!isset($param['dateEnd'])){
                $param['dateEnd']=date("Y-m-d");
            }
            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['channelKey'])) {
                $channel = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->get();
                if ($channel) {
                    $keyword['channelId'] = $channel->channelId;

                    $pageId = PageModel::create()->where(['pageName' => $param['channelKey']])->val('pageId');
                    $pageId && $psKeyword['ps.pageId'] = $clickKeyword['pageId'] = $pageId;

                } else {
                    // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                    return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
                }
            }
            isset($param['dateStart']) && $psKeyword['ps.dateStart'] = $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $psKeyword['ps.dateEnd'] = $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date',
                'channelId',
                'installAndroid',
                'realInstallAndroid',
                'activeAndroid',
                'realActiveAndroid',
                'installIOS',
                'realInstallIOS',
                'activeIOS',
                'realActiveIOS',
                'installIOSBookmark',
                'realInstallIOSBookmark',
                'activeIOSBookmark',
                'realActiveIOSBookmark',
                'installTotal',
                'realInstallTotal',
                'activeTotal',
                'realActiveTotal',
            ];

            $sortType = $param['sortType'] ?? '';
            //获取安装数
            $data = ChannelInstallStatisticModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);
            if($page==1){
                $nowTime=$keyword['dateEnd']; 
                $adClickStatucModel=adClickStatisticModel::create();
                if(isset($param['dateStart'])){
                    // $adClickStatucModel->where("date",$param['dateStart'],">=");
                    // $adClickStatucModel->where("date",$param['dateEnd'],"<=");
                }else{
                    $adClickStatucModel->where(["date"=>date("Y-m-d")]);
                }
                $adClickRes=$adClickStatucModel->getAll(1, $keyword, 100000, ["*"]);
                if($adClickRes){
                    foreach($adClickRes["list"] as $kad=>$vad){
                        $adkey=$vad->pageId."_".$vad->date;
                        $keyarr=[];
                        foreach($data["list"] as $kd=>$vd){
                            $keyarr[]=$vd->channelId."_".$vd->date;
                        }
                        if (!in_array($adkey, $keyarr)) {
                           
                            $newArray=[
                                "date"=>$vad->date,
                                "channelId"=>$vad->pageId,
                                "installAndroid"=>0,
                                "realInstallAndroid"=>0,
                                "activeAndroid"=>0,
                                "realActiveAndroid"=>0,
                                "installIOS"=>0,
                                "realInstallIOS"=>0,
                                "activeIOS"=>0,
                                "realActiveIOS"=>0,
                                "installIOSBookmark"=>0,
                                "realInstallIOSBookmark"=>0,
                                "activeIOSBookmark"=>0,
                                "realActiveIOSBookmark"=>0,
                                "installTotal"=>0,
                                "realInstallTotal"=>0,
                                "activeTotal"=>0,
                                "realActiveTotal"=>0,
                            ];
                            
                            array_unshift($data["list"], $newArray);
                           
                        }
                    }
                }
               
                
            }
           // return $this->writeJson(Status::CODE_OK,$data["list"], Status::getReasonPhrase(Status::CODE_OK));
            $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey', 'merchantId'], 'channelId', 'channelId');
            $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');

            /* 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比） begin */
            // 2023-11-13 增加了新增点击总数，留存点击总数，修改了新增点击比的算法。
            // 补充pageId的目的是为了拿点击数
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

            $acs = AdClickStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

            // 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            // $acsList = $acs
            //     ->field([
            //         'pageId',
            //         'date',
            //         'CONCAT(date,\'_\',pageId) AS dateKey',
            //         'IFNULL(SUM(clickCount),0) AS clickCount',
            //         'IFNULL(SUM(h5ClickCount),0) AS h5ClickCount', // h5点击数
            //         'IFNULL(SUM(appClickCount),0) AS appClickCount', // app点击数
            //         'IFNULL(SUM(retainedClickCount),0) AS retainedClickCount',
            //         //'IFNULL(SUM(clickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newClickCount',
            //         'IFNULL(SUM(appClickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newAppClickCount',
            //     ])
            //     ->where($where)
            //     ->group('date, pageId')
            //     ->indexBy('dateKey');

            //改版 begin
            $aps = PageStatisticModel::create();
            $pageWhere = $aps->parseKeywordToWhere($psKeyword);
            $dateList && $aps->where(['ps.date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            $apsList = $aps
                ->alias('ps')
                ->field([
                    'ps.date as date',
                    'ps.pageId as pageId',
                    'CONCAT(ps.date,\'_\',ps.pageId) AS dateKey',
                    'ps.ip',
                    'ps.reducedIp',
                    'p.ipCost as ipCost',
                    'ps.reducedIp * p.ipCost AS cost',
                    'IFNULL((ps.reducedIp * p.ipCost )/ SUM(acs.clickCount),0) AS clickCost', // 点击成本
                    'IFNULL(SUM(acs.h5ClickCount)/ps.ip,0) AS h5ClickRate', // h5点击比
                ])
                ->join(PageModel::create()->getTableName() . ' AS p', 'p.pageId = ps.pageId', 'LEFT')
                ->join(AdClickStatisticModel::create()->getTableName() . ' AS acs', 'acs.pageId = ps.pageId AND acs.date = ps.date', 'LEFT')
                ->where($pageWhere)
                ->group('date, pageId')
                ->indexBy('dateKey');
                // $this->writeJson(Status::CODE_OK, DbManager::getInstance()->getLastQuery()->getLastQuery(), Status::getReasonPhrase(Status::CODE_OK));
            $paymentDataGroup = UserVipOrderModel::create()->getGroupSum($clickKeyword, 'pageId');
            $paymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($clickKeyword, 'pageId');
            $appPaymentDataGroup = UserVipOrderModel::create()->getGroupSum($keyword, 'channelId');
            $appPaymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($keyword, 'channelId');
            //改版 end
            foreach ($data['list'] as $datum) {
                // 留存人数是单独减出来的，表里没有。
                $datum['retainedUserTotal'] = $datum['activeTotal'] - $datum['installTotal']; // 虚假留存
                $datum['realRetainedUserTotal'] = $datum['realActiveTotal'] - $datum['realInstallTotal']; // 真实留存

                // 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比）
                $clickCountKey = $datum['date'] . '_' . $datum['pageId'];
                $channelCountKey = $datum['date'] . '_' . $datum['channelId'];
                $datum['ip'] = $apsList[$clickCountKey]['ip'] ?? 0;
                $datum['reducedIp'] = $apsList[$clickCountKey]['reducedIp'] ?? 0;
                $datum['ipCost'] = $apsList[$clickCountKey]['ipCost'] ?? 0;
                $datum['cost'] = $apsList[$clickCountKey]['cost'] ?? 0;
                $datum['clickCost'] = $apsList[$clickCountKey]['clickCost'] ?? 0;
                $datum['h5ClickRate'] = $apsList[$clickCountKey]['h5ClickRate'] ?? 0;
                $datum['paymentUserCount'] = $paymentUserGroup[$clickCountKey]['userCount'] ?? 0;
                $datum['paymentOrderCount'] = $paymentDataGroup[$clickCountKey]['orderCount'] ?? 0;
                $datum['paymentOrderAmount'] = $paymentDataGroup[$clickCountKey]['amount'] ?? 0;
                $datum['appPaymentUserCount'] = $appPaymentUserGroup[$channelCountKey]['userCount'] ?? 0;
                $datum['appPaymentOrderCount'] = $appPaymentDataGroup[$channelCountKey]['orderCount'] ?? 0;
                $datum['appPaymentOrderAmount'] = $appPaymentDataGroup[$channelCountKey]['amount'] ?? 0;
                $datum['clickCount'] = $acsList[$clickCountKey]['clickCount'] ?? 0;
                $datum['h5ClickCount'] = $acsList[$clickCountKey]['h5ClickCount'] ?? 0;
                $datum['appClickCount'] = $acsList[$clickCountKey]['appClickCount'] ?? 0;
                $datum['retainedClickCount'] = $acsList[$clickCountKey]['retainedClickCount'] ?? 0;
                //$datum['newClickCount'] = $acsList[$clickCountKey]['newClickCount'] ?? 0;
                $datum['newAppClickCount'] = $acsList[$clickCountKey]['newAppClickCount'] ?? 0;
                $datum['installNewAppClickRatio'] = $datum['realInstallTotal'] > 0 ? bcdiv($datum['newAppClickCount'], $datum['realInstallTotal'], 2) : 0;
            }
            // $this->writeJson(Status::CODE_OK, $data['list'], Status::getReasonPhrase(Status::CODE_OK));
            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '渠道对应的总统计数据.xlsx';
                } else {
                    $fileName = '渠道对应的总统计数据.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['商户名', 'merchantName'],
                    ['渠道Key', 'channelKey'],
                    ['真实安卓安装', 'realInstallAndroid'],
                    ['真实安卓活跃', 'realActiveAndroid'],
                    ['真实ios书签安装', 'realInstallIOSBookmark'],
                    ['真实ios书签活跃', 'realActiveIOSBookmark'],
                    ['真实安装总数', 'realInstallTotal'],
                    ['进站ip', 'ip'],
                    ['扣量ip', 'reducedIp'],
                    ['ip单价', 'ipCost'],
                    ['投入成本', 'cost'],
                    ['点击成本', 'clickCost'],
                    ['总点击数', 'clickCount'],
                    ['h5点击数', 'h5ClickCount'],
                    ['h5点击比', 'h5ClickRate'],
                    ['app点击数', 'appClickCount'],
                    ['留存点击数', 'retainedClickCount'],
                    ['app拉单', 'appPaymentOrderCount'],
                    ['app成功数', 'appPaymentUserCount'],
                    ['app成功金额', 'appPaymentOrderAmount'],
                    ['h5拉单', 'paymentUserCount'],
                    ['h5成功数', 'paymentOrderCount'],
                    ['h5成功金额', 'paymentOrderAmount'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            }


            $acsListSum = AdClickStatisticModel::create()->getSum($clickKeyword);

            $data['sum'] = ChannelInstallStatisticModel::create()->getSum($keyword);

            // 留存人数是单独减出来的，表里没有。
            $data['sum']['realRetainedUserTotal'] = $data['sum']['realActiveTotal'] - $data['sum']['realInstallTotal'];
            $data['sum']['clickCount'] = $acsListSum['clickCount'];
            $data['sum']['h5ClickCount'] = $acsListSum['h5ClickCount'];
            $data['sum']['appClickCount'] = $acsListSum['appClickCount'];
            $data['sum']['retainedClickCount'] = $acsListSum['retainedClickCount'];
            $data['sum']['newAppClickCount'] = $acsListSum['newAppClickCount'];
            $data['sum']['installNewAppClickRatio'] = $data['sum']['realInstallTotal'] > 0 ? bcdiv($data['sum']['newAppClickCount'], $data['sum']['realInstallTotal'], 2) : 0;

            /* 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比） end */

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    // public function statisticListSystem()
    // {
    //     $param = $this->request()->getRequestParam();

    //     try {
    //         $keyword = [];
    //         $clickKeyword = [];
    //         $psKeyword = [];
    //         $page = (int)($param['page'] ?? 1);
    //         $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

    //         $param['export'] = $param['export'] ?? 0;
    //         if ($param['export']) {
    //             ini_set('memory_limit', '1024M');
    //             $pageSize = 300000;
    //         }

    //         if (isset($param['channelKey'])) {
    //             $channel = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->get();
    //             if ($channel) {
    //                 $keyword['channelId'] = $channel->channelId;

    //                 $pageId = PageModel::create()->where(['pageName' => $param['channelKey']])->val('pageId');
    //                 $pageId && $psKeyword['ps.pageId'] = $clickKeyword['pageId'] = $pageId;

    //             } else {
    //                 // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
    //                 return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    //             }
    //         }
    //         isset($param['dateStart']) && $psKeyword['ps.dateStart'] = $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
    //         isset($param['dateEnd']) && $psKeyword['ps.dateEnd'] = $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

    //         $field = [
    //             'date',
    //             'channelId',
    //             'installAndroid',
    //             'realInstallAndroid',
    //             'activeAndroid',
    //             'realActiveAndroid',
    //             'installIOS',
    //             'realInstallIOS',
    //             'activeIOS',
    //             'realActiveIOS',
    //             'installIOSBookmark',
    //             'realInstallIOSBookmark',
    //             'activeIOSBookmark',
    //             'realActiveIOSBookmark',
    //             'installTotal',
    //             'realInstallTotal',
    //             'activeTotal',
    //             'realActiveTotal',
    //         ];

    //         $sortType = $param['sortType'] ?? '';
    //         //获取安装数
    //         $data = ChannelInstallStatisticModel::create()
    //             ->setOrderType($sortType)
    //             ->getAll($page, $keyword, $pageSize, $field);
    //         $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey', 'merchantId'], 'channelId', 'channelId');
    //         $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');

    //         /* 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比） begin */
    //         // 2023-11-13 增加了新增点击总数，留存点击总数，修改了新增点击比的算法。
    //         // 补充pageId的目的是为了拿点击数
    //         $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

    //         $acs = AdClickStatisticModel::create();
    //         $where = $acs->parseKeywordToWhere($clickKeyword);

    //         // 因为有分页，查询关联数据也要处理一下查询条件
    //         $dateList = array_unique(array_column($data['list'], 'date'));
    //         $dateList && $acs->where(['date' => [$dateList, 'IN']]);
    //         // 查询分页条件内的日期的关联点击数据
    //         $acsList = $acs
    //             ->field([
    //                 'pageId',
    //                 'date',
    //                 'CONCAT(date,\'_\',pageId) AS dateKey',
    //                 'IFNULL(SUM(clickCount),0) AS clickCount',
    //                 'IFNULL(SUM(h5ClickCount),0) AS h5ClickCount', // h5点击数
    //                 'IFNULL(SUM(appClickCount),0) AS appClickCount', // app点击数
    //                 'IFNULL(SUM(retainedClickCount),0) AS retainedClickCount',
    //                 //'IFNULL(SUM(clickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newClickCount',
    //                 'IFNULL(SUM(appClickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newAppClickCount',
    //             ])
    //             ->where($where)
    //             ->group('date, pageId')
    //             ->indexBy('dateKey');

    //         //改版 begin
    //         $aps = PageStatisticModel::create();
    //         $pageWhere = $aps->parseKeywordToWhere($psKeyword);
    //         $dateList && $aps->where(['ps.date' => [$dateList, 'IN']]);
    //         // 查询分页条件内的日期的关联点击数据
    //         $apsList = $aps
    //             ->alias('ps')
    //             ->field([
    //                 'ps.date as date',
    //                 'ps.pageId as pageId',
    //                 'CONCAT(ps.date,\'_\',ps.pageId) AS dateKey',
    //                 'ps.ip',
    //                 'ps.reducedIp',
    //                 'p.ipCost as ipCost',
    //                 'ps.reducedIp * p.ipCost AS cost',
    //                 'IFNULL((ps.reducedIp * p.ipCost )/ SUM(acs.clickCount),0) AS clickCost', // 点击成本
    //                 'IFNULL(SUM(acs.h5ClickCount)/ps.ip,0) AS h5ClickRate', // h5点击比
    //             ])
    //             ->join(PageModel::create()->getTableName() . ' AS p', 'p.pageId = ps.pageId', 'LEFT')
    //             ->join(AdClickStatisticModel::create()->getTableName() . ' AS acs', 'acs.pageId = ps.pageId AND acs.date = ps.date', 'LEFT')
    //             ->where($pageWhere)
    //             ->group('date, pageId')
    //             ->indexBy('dateKey');
    //             // $this->writeJson(Status::CODE_OK, DbManager::getInstance()->getLastQuery()->getLastQuery(), Status::getReasonPhrase(Status::CODE_OK));
    //         $paymentDataGroup = UserVipOrderModel::create()->getGroupSum($clickKeyword, 'pageId');
    //         $paymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($clickKeyword, 'pageId');
    //         $appPaymentDataGroup = UserVipOrderModel::create()->getGroupSum($keyword, 'channelId');
    //         $appPaymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($keyword, 'channelId');
    //         //改版 end
    //         foreach ($data['list'] as $datum) {
    //             // 留存人数是单独减出来的，表里没有。
    //             $datum['retainedUserTotal'] = $datum['activeTotal'] - $datum['installTotal']; // 虚假留存
    //             $datum['realRetainedUserTotal'] = $datum['realActiveTotal'] - $datum['realInstallTotal']; // 真实留存

    //             // 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比）
    //             $clickCountKey = $datum['date'] . '_' . $datum['pageId'];
    //             $channelCountKey = $datum['date'] . '_' . $datum['channelId'];
    //             $datum['ip'] = $apsList[$clickCountKey]['ip'] ?? 0;
    //             $datum['reducedIp'] = $apsList[$clickCountKey]['reducedIp'] ?? 0;
    //             $datum['ipCost'] = $apsList[$clickCountKey]['ipCost'] ?? 0;
    //             $datum['cost'] = $apsList[$clickCountKey]['cost'] ?? 0;
    //             $datum['clickCost'] = $apsList[$clickCountKey]['clickCost'] ?? 0;
    //             $datum['h5ClickRate'] = $apsList[$clickCountKey]['h5ClickRate'] ?? 0;
    //             $datum['paymentUserCount'] = $paymentUserGroup[$clickCountKey]['userCount'] ?? 0;
    //             $datum['paymentOrderCount'] = $paymentDataGroup[$clickCountKey]['orderCount'] ?? 0;
    //             $datum['paymentOrderAmount'] = $paymentDataGroup[$clickCountKey]['amount'] ?? 0;
    //             $datum['appPaymentUserCount'] = $appPaymentUserGroup[$channelCountKey]['userCount'] ?? 0;
    //             $datum['appPaymentOrderCount'] = $appPaymentDataGroup[$channelCountKey]['orderCount'] ?? 0;
    //             $datum['appPaymentOrderAmount'] = $appPaymentDataGroup[$channelCountKey]['amount'] ?? 0;
    //             $datum['clickCount'] = $acsList[$clickCountKey]['clickCount'] ?? 0;
    //             $datum['h5ClickCount'] = $acsList[$clickCountKey]['h5ClickCount'] ?? 0;
    //             $datum['appClickCount'] = $acsList[$clickCountKey]['appClickCount'] ?? 0;
    //             $datum['retainedClickCount'] = $acsList[$clickCountKey]['retainedClickCount'] ?? 0;
    //             //$datum['newClickCount'] = $acsList[$clickCountKey]['newClickCount'] ?? 0;
    //             $datum['newAppClickCount'] = $acsList[$clickCountKey]['newAppClickCount'] ?? 0;
    //             $datum['installNewAppClickRatio'] = $datum['realInstallTotal'] > 0 ? bcdiv($datum['newAppClickCount'], $datum['realInstallTotal'], 2) : 0;
    //         }
    //         // $this->writeJson(Status::CODE_OK, $data['list'], Status::getReasonPhrase(Status::CODE_OK));
    //         if ($param['export']) {
    //             if (!$data['list']) {
    //                 throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
    //             }

    //             if (isset($param['channelKey'])) {
    //                 $fileName = $param['channelKey'] . '渠道对应的总统计数据.xlsx';
    //             } else {
    //                 $fileName = '渠道对应的总统计数据.xlsx';
    //             }
    //             $headers = [
    //                 ['日期', 'date'],
    //                 ['商户名', 'merchantName'],
    //                 ['渠道Key', 'channelKey'],
    //                 ['真实安卓安装', 'realInstallAndroid'],
    //                 ['真实安卓活跃', 'realActiveAndroid'],
    //                 ['真实ios书签安装', 'realInstallIOSBookmark'],
    //                 ['真实ios书签活跃', 'realActiveIOSBookmark'],
    //                 ['真实安装总数', 'realInstallTotal'],
    //                 ['进站ip', 'ip'],
    //                 ['扣量ip', 'reducedIp'],
    //                 ['ip单价', 'ipCost'],
    //                 ['投入成本', 'cost'],
    //                 ['点击成本', 'clickCost'],
    //                 ['总点击数', 'clickCount'],
    //                 ['h5点击数', 'h5ClickCount'],
    //                 ['h5点击比', 'h5ClickRate'],
    //                 ['app点击数', 'appClickCount'],
    //                 ['留存点击数', 'retainedClickCount'],
    //                 ['app拉单', 'appPaymentOrderCount'],
    //                 ['app成功数', 'appPaymentUserCount'],
    //                 ['app成功金额', 'appPaymentOrderAmount'],
    //                 ['h5拉单', 'paymentUserCount'],
    //                 ['h5成功数', 'paymentOrderCount'],
    //                 ['h5成功金额', 'paymentOrderAmount'],
    //             ];
    //             $this->downloadExcel($headers, $data['list'], $fileName);
    //         }


    //         $acsListSum = AdClickStatisticModel::create()->getSum($clickKeyword);

    //         $data['sum'] = ChannelInstallStatisticModel::create()->getSum($keyword);

    //         // 留存人数是单独减出来的，表里没有。
    //         $data['sum']['realRetainedUserTotal'] = $data['sum']['realActiveTotal'] - $data['sum']['realInstallTotal'];
    //         $data['sum']['clickCount'] = $acsListSum['clickCount'];
    //         $data['sum']['h5ClickCount'] = $acsListSum['h5ClickCount'];
    //         $data['sum']['appClickCount'] = $acsListSum['appClickCount'];
    //         $data['sum']['retainedClickCount'] = $acsListSum['retainedClickCount'];
    //         $data['sum']['newAppClickCount'] = $acsListSum['newAppClickCount'];
    //         $data['sum']['installNewAppClickRatio'] = $data['sum']['realInstallTotal'] > 0 ? bcdiv($data['sum']['newAppClickCount'], $data['sum']['realInstallTotal'], 2) : 0;

    //         /* 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比） end */

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }

    //     return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    // }


    /**
     * 渠道成本统计列表（管理员）
     * @Api(name="渠道成本统计列表（管理员）",path="/Api/Admin/Merchant/Channel/costStatisticListSystem")
     * @ApiDescription("渠道成本统计列表（管理员）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", description="1.时间倒序（date_DESC）2.时间正序（date_ASC）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-16","channelId":18,"installAndroid":1,"realInstallAndroid":1,"activeAndroid":3,"realActiveAndroid":3,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":1,"realInstallTotal":1,"activeTotal":3,"realActiveTotal":3,"channelKey":"test.html","merchantId":1,"merchantName":"测试商户","pageId":2,"retainedUserTotal":2,"realRetainedUserTotal":2,"clickCount":"9","h5ClickCount":"7","appClickCount":"2","retainedClickCount":"2","newAppClickCount":"0","installNewAppClickRatio":"0.00"},{"date":"2023-11-16","channelId":1,"installAndroid":3,"realInstallAndroid":6,"activeAndroid":10,"realActiveAndroid":13,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":3,"realInstallTotal":6,"activeTotal":10,"realActiveTotal":13,"channelKey":"index.html","merchantId":1,"merchantName":"测试商户","pageId":1,"retainedUserTotal":7,"realRetainedUserTotal":7,"clickCount":"8","h5ClickCount":"4","appClickCount":"4","retainedClickCount":"4","newAppClickCount":"0","installNewAppClickRatio":"0.00"},{"date":"2023-11-15","channelId":1,"installAndroid":3,"realInstallAndroid":3,"activeAndroid":5,"realActiveAndroid":5,"installIOS":0,"realInstallIOS":0,"activeIOS":0,"realActiveIOS":0,"installIOSBookmark":0,"realInstallIOSBookmark":0,"activeIOSBookmark":0,"realActiveIOSBookmark":0,"installTotal":3,"realInstallTotal":3,"activeTotal":5,"realActiveTotal":5,"channelKey":"index.html","merchantId":1,"merchantName":"测试商户","pageId":1,"retainedUserTotal":2,"realRetainedUserTotal":2,"clickCount":"4","h5ClickCount":"1","appClickCount":"3","retainedClickCount":"3","newAppClickCount":"0","installNewAppClickRatio":"0.00"}],"options":{"dateStart":"2023-11-01","dateEnd":"2023-11-28"},"sum":{"installAndroid":"7","realInstallAndroid":"10","activeAndroid":"18","realActiveAndroid":"21","installIOS":"0","realInstallIOS":"0","activeIOS":"0","realActiveIOS":"0","installIOSBookmark":"0","realInstallIOSBookmark":"0","activeIOSBookmark":"0","realActiveIOSBookmark":"0","installTotal":"7","realInstallTotal":"10","activeTotal":"18","realActiveTotal":"21","realRetainedUserTotal":11,"clickCount":"21","h5ClickCount":"12","appClickCount":"9","retainedClickCount":"9","newAppClickCount":"0","installNewAppClickRatio":"0.00"}},"systemTimestamp":1701179336,"systemDateTime":"2023-11-28 21:48:56","msg":"OK"})
     */
    public function costStatisticListSystem()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $clickKeyword = [];
            $psKeyword = [];
            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
            }

            if (isset($param['channelKey'])) {
                $channel = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->get();
                if ($channel) {
                    $keyword['channelId'] = $channel->channelId;

                    $pageId = PageModel::create()->where(['pageName' => $param['channelKey']])->val('pageId');
                    $pageId && $psKeyword['ps.pageId'] = $clickKeyword['pageId'] = $pageId;

                } else {
                    // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                    return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
                }
            }
            $clickKeyword['dateStart'] = date('Y-m-d');
            $clickKeyword['dateEnd'] = date('Y-m-d');
            isset($param['dateStart']) &&  $clickKeyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd'])  && $clickKeyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));
            $field = [
                'channelCostId',
                'channelId',
                'channelKey',
                'cost',
                'apiUrl',
                'dhClick',
                'dhJson',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ChannelCostStatisticModel::create()
                ->setOrderType($sortType)
                ->getAll(1, $keyword, 50, $field);
            $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey', 'merchantId'], 'channelId', 'channelId');
            $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');

            /* 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比） begin */
            // 2023-11-13 增加了新增点击总数，留存点击总数，修改了新增点击比的算法。
            // 补充pageId的目的是为了拿点击数
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

            $acs = AdClickStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

            // 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            $acsList = $acs
                ->field([
                    'pageId',
                    'date',
                    'CONCAT(date,\'_\',pageId) AS dateKey',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where($where)
                ->group('date, pageId')
                ->indexBy('dateKey');

            foreach ($data['list'] as $datum) {
                // 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比）
                $clickCountKey = $clickKeyword['dateStart'] . '_' . $datum['pageId'];
                $dhClickArr = json_decode($datum['dhJson'], true);
                $datum['dhClickCount'] = $dhClickArr[$clickKeyword['dateStart']] ?? 0;
                $datum['clickCount'] = $acsList[$clickCountKey]['clickCount'] ?? 0;
                $datum['inputCost'] = floor($datum['dhClickCount'] * $datum['cost']);
                $clickCost = $datum['clickCount'] > 0 ? $datum['dhClickCount'] * $datum['cost'] / $datum['clickCount'] : 0;
                $datum['clickCost'] = $clickCost > 0 ? sprintf("%.2f", $clickCost) : $clickCost;
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '渠道成本统计数据.xlsx';
                } else {
                    $fileName = '渠道成本统计数据.xlsx';
                }
                $headers = [
                    ['商户名', 'merchantName'],
                    ['渠道Key', 'channelKey'],
                    ['导航点击', 'dhClickCount'],
                    ['保底单价', 'cost'],
                    ['投入成本', 'inputCost'],
                    ['站内点击', 'clickCount'],
                    ['点击成本', 'clickCost'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道成本编辑
     * @Api(name="渠道成本编辑",path="/Api/Admin/Merchant/Channel/costEdit")
     * @ApiDescription("渠道成本编辑")
     * @Method(allow=["POST"])
     * @Param(name="channelCostId", alias="渠道成本id", type="int", required="", min="1", description="渠道成本id")
     * @Param(name="apiUrl", alias="渠道api", type="string", required="", description="渠道api")
     * @Param(name="cost", alias="保底单价", type="float", required="", min="0", description="保底单价")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function costEdit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'apiUrl' => trim($param['apiUrl']),
                'cost' => trim($param['cost']),
            ];

            $result = ChannelCostStatisticModel::create()
                ->where([
                    'channelCostId' => $param['channelCostId'],
                ])
                ->update($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 渠道推广统计列表app安装版（管理员）
     * @Api(name="渠道推广统计列表app安装版（管理员）也是 渠道推广统计列表2",path="/Api/Admin/Merchant/Channel/statisticListForAppSystem")
     * @ApiDescription("渠道推广统计列表app安装版（管理员）也是 渠道推广统计列表2")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["channelId_DESC","channelId_ASC","installAndroid_DESC","installAndroid_ASC","realInstallAndroid_DESC","realInstallAndroid_ASC","activeAndroid_DESC","activeAndroid_ASC","realActiveAndroid_DESC","realActiveAndroid_ASC","installIOS_DESC","installIOS_ASC","realInstallIOS_DESC","realInstallIOS_ASC","activeIOS_DESC","activeIOS_ASC","realActiveIOS_DESC","realActiveIOS_ASC","installIOSBookmark_DESC","installIOSBookmark_ASC","realInstallIOSBookmark_DESC","realInstallIOSBookmark_ASC","activeIOSBookmark_DESC","activeIOSBookmark_ASC","realActiveIOSBookmark_DESC","realActiveIOSBookmark_ASC","installTotal_DESC","installTotal_ASC","realInstallTotal_DESC","realInstallTotal_ASC","activeTotal_DESC","activeTotal_ASC","realActiveTotal_DESC","realActiveTotal_ASC","date_DESC", "date_ASC", "totalCost_DESC", "totalCost_ASC"], description="1.日期（date）2.投入成本（totalCost）3.真实总安装（realInstallTotal）4.扣量后总安装（installTotal）5.真实总活跃（realActiveTotal）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-16","channelId":1,"realInstallTotal":6,"installTotal":3,"realActiveTotal":13,"channelKey":"index.html","merchantName":"测试商户","totalCost":0,"cost":0,"realCost":0,"pageId":1,"newAppClickCount":"0","newAppClickCost":0,"installNewAppClickRatio":"0.00","clickCount":"8","h5ClickCount":"4","appClickCount":"4","totalClickCost":"0.00","totalClickRatio":"0.61","paymentUserCount":1,"paymentOrderCount":1,"paymentOrderAmount":"50.00"}],"options":{"dateStart":"2023-11-15","dateEnd":"2023-11-16"},"sum":{"installAndroid":"7","realInstallAndroid":"10","activeAndroid":"18","realActiveAndroid":"21","installIOS":"0","realInstallIOS":"0","activeIOS":"0","realActiveIOS":"0","installIOSBookmark":"0","realInstallIOSBookmark":"0","activeIOSBookmark":"0","realActiveIOSBookmark":"0","installTotal":"7","realInstallTotal":"10","activeTotal":"18","realActiveTotal":"21","realRetainedUserTotal":11,"clickCount":"21","h5ClickCount":"12","appClickCount":"9","retainedClickCount":"9","newAppClickCount":"0","paymentUserCount":2,"paymentOrderCount":2,"paymentOrderAmount":"250.00"}},"systemTimestamp":1702459934,"systemDateTime":"2023-12-13 17:32:14","msg":"OK"})
     */
    public function statisticListForAppSystem()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $channelKeyword = [];
            $clickKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['channelKey'])) {
                // 2023-12-25 渠道key改为只有后%
                $channelList = ChannelModel::create()->field(['channelId', 'channelKey'])->where(['channelKey' => [$param['channelKey'] . '%', 'LIKE']])->all();
                if (!$channelList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelIdList = array_column($channelList, 'channelId');
                $keyword['c.channelId'] = $channelIdList;
                $channelKeyword['channelId'] = $channelIdList;

                $pageIdList = PageModel::create()->where(['pageName' => [array_column($channelList, 'channelKey'), 'IN']])->column('pageId');
                $clickKeyword['pageId'] = $pageIdList;
            }

            if (isset($param['merchantName'])) {
                $merchant = MerchantModel::create()->get(['merchantName' => $param['merchantName']]);
                if (!$merchant) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelList = ChannelModel::create()->field(['channelId', 'channelKey'])->where(['merchantId' => $merchant->merchantId])->all();
                if (!$channelList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelIdList = array_column($channelList, 'channelId');
                $keyword['c.channelId'] = $channelIdList;
                $channelKeyword['channelId'] = $channelIdList;

                $pageIdList = PageModel::create()->where(['pageName' => [array_column($channelList, 'channelKey'), 'IN']])->column('pageId');
                $clickKeyword['pageId'] = $pageIdList;
            }

            isset($param['dateStart']) && $channelKeyword['dateStart'] = $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $channelKeyword['dateEnd'] = $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'cis.date', // 日期
                'cis.channelId', // 渠道id
                'c.channelKey', // 渠道key
                'm.merchantName', // 商户名
                '(c.cost * cis.installTotal) AS totalCost', // 投入成本
                'cis.realInstallTotal', // 实际总安装数
                'cis.installTotal', // 扣量后总安装数
                'cis.realActiveTotal', // 真实总活跃数
                'c.cost', // 单价
                '(c.cost * cis.installTotal / cis.realInstallTotal) AS realCost', // 实际单价
            ];

            $sortType = $param['sortType'] ?? '';

            $cis = ChannelInstallStatisticModel::create();
            $data = $cis
                ->alias('cis')
                ->join(ChannelModel::create()->getTableName() . ' AS c', 'cis.channelId = c.channelId', 'LEFT')
                ->join(MerchantModel::create()->getTableName() . ' AS m', 'c.merchantId = m.merchantId', 'LEFT')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            // 补充pageId的目的是为了拿点击数
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

            $acs = AdClickStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

            // 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            $acsList = $acs
                ->field([
                    'pageId',
                    'date',
                    'CONCAT(date,\'_\',pageId) AS dateKey',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                    'IFNULL(SUM(h5ClickCount),0) AS h5ClickCount', // h5点击数
                    'IFNULL(SUM(appClickCount),0) AS appClickCount', // app点击数
                    'IFNULL(SUM(retainedClickCount),0) AS retainedClickCount',
                    //'IFNULL(SUM(clickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newClickCount',
                    'IFNULL(SUM(appClickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newAppClickCount',
                ])
                ->where($where)
                ->group('date, pageId')
                ->indexBy('dateKey');

            // 2023-12-12 增加渠道对应的支付数据
            # TODO: 这里只写了用户VIP购买订单，如果要加其他的订单要写一个订单聚合统计
            $paymentDataGroup = UserVipOrderModel::create()->getGroupSum($channelKeyword, 'channelId');
            $paymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($channelKeyword, 'channelId');

            foreach ($data['list'] as $datum) {
                $datePageKey = $datum['date'] . '_' . $datum['pageId'];
                $dateChannelKey = $datum['date'] . '_' . $datum['channelId'];
                // app新增点击数
                $datum['newAppClickCount'] = $acsList[$datePageKey]['newAppClickCount'] ?? 0;
                // app新增点击成本（投入成本/app新增点击数）
                $datum['newAppClickCost'] = $datum['newAppClickCount'] > 0 ? bcdiv($datum['totalCost'], $datum['newAppClickCount'], 2) : 0;
                // 新增点击比（app新增点击数/实际安装数）
                $datum['installNewAppClickRatio'] = $datum['realInstallTotal'] > 0 ? bcdiv($datum['newAppClickCount'], $datum['realInstallTotal'], 2) : 0;
                // 点击总数
                $datum['clickCount'] = $acsList[$datePageKey]['clickCount'] ?? 0;
                // h5点击总数
                $datum['h5ClickCount'] = $acsList[$datePageKey]['h5ClickCount'] ?? 0;
                // app点击总数
                $datum['appClickCount'] = $acsList[$datePageKey]['appClickCount'] ?? 0;
                // 总点击成本（投入成本/总点击）
                $datum['totalClickCost'] = $datum['clickCount'] > 0 ? bcdiv($datum['totalCost'], $datum['clickCount'], 2) : 0;
                // 总点击比（总点击/总活跃数）
                $datum['totalClickRatio'] = $datum['realActiveTotal'] > 0 ? bcdiv($datum['clickCount'], $datum['realActiveTotal'], 2) : 0;

                // 保留2位小数
                $datum['totalCost'] = floor($datum['totalCost'] * 100) / 100;
                $datum['cost'] = floor($datum['cost'] * 100) / 100;
                $datum['realCost'] = floor($datum['realCost'] * 100) / 100;

                // 2023-12-12 增加渠道对应的支付数据
                $datum['paymentUserCount'] = $paymentUserGroup[$dateChannelKey]['userCount'] ?? 0;
                $datum['paymentOrderCount'] = $paymentDataGroup[$dateChannelKey]['orderCount'] ?? 0;
                $datum['paymentOrderAmount'] = $paymentDataGroup[$dateChannelKey]['amount'] ?? 0;
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '渠道推广统计列表app安装版.xlsx';
                } else {
                    $fileName = '渠道推广统计列表app安装版.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['渠道id', 'channelId'],
                    ['渠道key', 'channelKey'],
                    ['所属商户', 'merchantName'],
                    ['投入成本', 'totalCost'],
                    ['实际安装数', 'realInstallTotal'],
                    ['扣量后安装数', 'installTotal'],
                    ['实际活跃数', 'realActiveTotal'],
                    ['单价', 'cost'],
                    ['实际单价', 'realCost'],
                    ['app新增点击数', 'newAppClickCount'],
                    ['app新增点击成本', 'newAppClickCost'],
                    ['新增点击比', 'installNewAppClickRatio'],
                    ['点击总数', 'clickCount'],
                    ['h5点击总数', 'h5ClickCount'],
                    ['app点击总数', 'appClickCount'],
                    ['总点击成本', 'totalClickCost'],
                    ['总点击比', 'totalClickRatio'],
                    ['拉单人数', 'paymentUserCount'],
                    ['成功订单数', 'paymentOrderCount'],
                    ['成功订单金额', 'paymentOrderAmount'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                // 查询筛选时间内的点击总计
                $acsListSum = AdClickStatisticModel::create()->getSum($clickKeyword);

                $data['sum'] = ChannelInstallStatisticModel::create()->getSum($channelKeyword);
                // 留存人数是单独减出来的，表里没有。
                $data['sum']['realRetainedUserTotal'] = $data['sum']['realActiveTotal'] - $data['sum']['realInstallTotal'];
                $data['sum']['clickCount'] = $acsListSum['clickCount'];
                $data['sum']['h5ClickCount'] = $acsListSum['h5ClickCount'];
                $data['sum']['appClickCount'] = $acsListSum['appClickCount'];
                $data['sum']['retainedClickCount'] = $acsListSum['retainedClickCount'];
                $data['sum']['newAppClickCount'] = $acsListSum['newAppClickCount'];

                // 2023-12-12 增加渠道对应的支付数据
                $paymentUserSum = UserVipOrderModel::create()->getUserCount($channelKeyword, 'channelId');
                $data['sum']['paymentUserCount'] = $paymentUserSum['userCount'];
                $paymentDataSum = UserVipOrderModel::create()->getSum($channelKeyword, 'channelId');
                $data['sum']['paymentOrderCount'] = $paymentDataSum['orderCount'];
                $data['sum']['paymentOrderAmount'] = $paymentDataSum['amount'];
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * CPA渠道总计看板（管理员）
     * @Api(name="CPA渠道总计看板（管理员）",path="/Api/Admin/Merchant/Channel/cpaStatisticList")
     * @ApiDescription("CPA渠道总计看板")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="500", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["channelId_DESC","channelId_ASC","installAndroid_DESC","installAndroid_ASC","realInstallAndroid_DESC","realInstallAndroid_ASC","activeAndroid_DESC","activeAndroid_ASC","realActiveAndroid_DESC","realActiveAndroid_ASC","installIOS_DESC","installIOS_ASC","realInstallIOS_DESC","realInstallIOS_ASC","activeIOS_DESC","activeIOS_ASC","realActiveIOS_DESC","realActiveIOS_ASC","installIOSBookmark_DESC","installIOSBookmark_ASC","realInstallIOSBookmark_DESC","realInstallIOSBookmark_ASC","activeIOSBookmark_DESC","activeIOSBookmark_ASC","realActiveIOSBookmark_DESC","realActiveIOSBookmark_ASC","installTotal_DESC","installTotal_ASC","realInstallTotal_DESC","realInstallTotal_ASC","activeTotal_DESC","activeTotal_ASC","realActiveTotal_DESC","realActiveTotal_ASC","date_DESC", "date_ASC", "totalCost_DESC", "totalCost_ASC"], description="1.日期（date）2.投入成本（totalCost）3.真实总安装（realInstallTotal）4.扣量后总安装（installTotal）5.真实总活跃（realActiveTotal）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-16","channelId":1,"realInstallTotal":6,"installTotal":3,"realActiveTotal":13,"channelKey":"index.html","merchantName":"测试商户","totalCost":0,"cost":0,"realCost":0,"pageId":1,"newAppClickCount":"0","newAppClickCost":0,"installNewAppClickRatio":"0.00","clickCount":"8","h5ClickCount":"4","appClickCount":"4","totalClickCost":"0.00","totalClickRatio":"0.61","paymentUserCount":1,"paymentOrderCount":1,"paymentOrderAmount":"50.00"}],"options":{"dateStart":"2023-11-15","dateEnd":"2023-11-16"},"sum":{"installAndroid":"7","realInstallAndroid":"10","activeAndroid":"18","realActiveAndroid":"21","installIOS":"0","realInstallIOS":"0","activeIOS":"0","realActiveIOS":"0","installIOSBookmark":"0","realInstallIOSBookmark":"0","activeIOSBookmark":"0","realActiveIOSBookmark":"0","installTotal":"7","realInstallTotal":"10","activeTotal":"18","realActiveTotal":"21","realRetainedUserTotal":11,"clickCount":"21","h5ClickCount":"12","appClickCount":"9","retainedClickCount":"9","newAppClickCount":"0","paymentUserCount":2,"paymentOrderCount":2,"paymentOrderAmount":"250.00"}},"systemTimestamp":1702459934,"systemDateTime":"2023-12-13 17:32:14","msg":"OK"})
     */
    public function cpaStatisticList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $channelKeyword = [];
            $clickKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = 500;
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['channelKey'])) {
                // 2023-12-25 渠道key改为只有后%
                $channelList = ChannelModel::create()->field(['channelId', 'channelKey'])->where(['channelKey' => [$param['channelKey'] . '%', 'LIKE']])->all();
                if (!$channelList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelIdList = array_column($channelList, 'channelId');
                $keyword['c.channelId'] = $channelIdList;
                $channelKeyword['channelId'] = $channelIdList;

                $pageIdList = PageModel::create()->where(['pageName' => [array_column($channelList, 'channelKey'), 'IN']])->column('pageId');
                $clickKeyword['pageId'] = $pageIdList;
            }

            if (isset($param['merchantName'])) {
                $merchant = MerchantModel::create()->get(['merchantName' => $param['merchantName']]);
                if (!$merchant) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelList = ChannelModel::create()->field(['channelId', 'channelKey'])->where(['merchantId' => $merchant->merchantId])->all();
                if (!$channelList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelIdList = array_column($channelList, 'channelId');
                $keyword['c.channelId'] = $channelIdList;
                $channelKeyword['channelId'] = $channelIdList;

                $pageIdList = PageModel::create()->where(['pageName' => [array_column($channelList, 'channelKey'), 'IN']])->column('pageId');
                $clickKeyword['pageId'] = $pageIdList;
            }

            isset($param['dateStart']) && $channelKeyword['dateStart'] = $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $channelKeyword['dateEnd'] = $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'cis.date', // 日期
                'cis.channelId', // 渠道id
                'c.channelKey', // 渠道key
                'm.merchantName', // 商户名
                'c.cpaCost', // cpa单价
                'c.coefficient', // 点击系数
            ];

            $sortType = $param['sortType'] ?? '';

            $cis = ChannelInstallStatisticModel::create();
            $data = $cis
                ->alias('cis')
                ->join(ChannelModel::create()->getTableName() . ' AS c', 'cis.channelId = c.channelId', 'LEFT')
                ->join(MerchantModel::create()->getTableName() . ' AS m', 'c.merchantId = m.merchantId', 'LEFT')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            // 补充pageId的目的是为了拿点击数
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

            $acs = AdClickStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

            // 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            $acsList = $acs
                ->field([
                    'pageId',
                    'date',
                    'CONCAT(date,\'_\',pageId) AS dateKey',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where($where)
                ->group('date, pageId')
                ->indexBy('dateKey');
            $installSum = 0;
            $clickCountSum = 0;
            foreach ($data['list'] as $datum) {
                $datePageKey = $datum['date'] . '_' . $datum['pageId'];
                // 点击总数
                $datum['clickCount'] = $acsList[$datePageKey]['clickCount'] ?? 0;

                $install = $datum['cpaCost'] > 0 ? $datum['coefficient'] * $datum['clickCount'] / $datum['cpaCost'] : 0;
                $datum['install'] = floor($install);

                $datum['totalCost'] = $datum['cpaCost'] * $datum['install'];
                $datum['totalClickCost'] = $datum['clickCount'] > 0 ? bcdiv($datum['totalCost'], $datum['clickCount'], 2) : 0;
                $installSum += $datum['install'];
                $clickCountSum += $datum['clickCount'];
            }
            $filterList = array_filter($data['list'], function($item) {
                return $item['clickCount'] !== 0;
            });
            $data['list'] = array_values($filterList);
            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '渠道推广统计列表app安装版.xlsx';
                } else {
                    $fileName = '渠道推广统计列表app安装版.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['渠道id', 'channelId'],
                    ['渠道key', 'channelKey'],
                    ['所属商户', 'merchantName'],
                    ['点击总数', 'clickCount'],
                    ['CPA数', 'install'],
                    ['花费', 'totalCost'],
                    ['点击成本', 'totalClickCost'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                $data['sum']['install'] = $installSum;
                // 留存人数是单独减出来的，表里没有。
                $data['sum']['clickCount'] = $clickCountSum;
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 点击下载按钮统计（管理员）
     * @Api(name="点击下载按钮统计（管理员）",path="/Api/Admin/Merchant/Channel/downloadStatistic")
     * @ApiDescription("点击下载按钮统计（管理员）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["channelId_DESC","channelId_ASC","installAndroid_DESC","installAndroid_ASC","realInstallAndroid_DESC","realInstallAndroid_ASC","activeAndroid_DESC","activeAndroid_ASC","realActiveAndroid_DESC","realActiveAndroid_ASC","installIOS_DESC","installIOS_ASC","realInstallIOS_DESC","realInstallIOS_ASC","activeIOS_DESC","activeIOS_ASC","realActiveIOS_DESC","realActiveIOS_ASC","installIOSBookmark_DESC","installIOSBookmark_ASC","realInstallIOSBookmark_DESC","realInstallIOSBookmark_ASC","activeIOSBookmark_DESC","activeIOSBookmark_ASC","realActiveIOSBookmark_DESC","realActiveIOSBookmark_ASC","installTotal_DESC","installTotal_ASC","realInstallTotal_DESC","realInstallTotal_ASC","activeTotal_DESC","activeTotal_ASC","realActiveTotal_DESC","realActiveTotal_ASC","date_DESC", "date_ASC", "totalCost_DESC", "totalCost_ASC"], description="1.日期（date）2.投入成本（totalCost）3.真实总安装（realInstallTotal）4.扣量后总安装（installTotal）5.真实总活跃（realActiveTotal）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":32,"list":[{"date":"2024-05-26","channelId":1976,"downClick":1,"channelKey":"lm0","merchantName":"lm0","realInstallTotal":"1"},{"date":"2024-05-26","channelId":1924,"downClick":28,"channelKey":"caoliu","merchantName":"草榴社区","realInstallTotal":"26"},{"date":"2024-05-26","channelId":1553,"downClick":1,"channelKey":"tiaozhuan","merchantName":"域名跳转","realInstallTotal":"0"},{"date":"2024-05-26","channelId":1548,"downClick":15,"channelKey":"chunqu","merchantName":"春趣导航","realInstallTotal":"22"},{"date":"2024-05-26","channelId":1546,"downClick":25,"channelKey":"sejie2","merchantName":"色界导航","realInstallTotal":"35"},{"date":"2024-05-26","channelId":1541,"downClick":27,"channelKey":"setiantang","merchantName":"色天堂2","realInstallTotal":"34"},{"date":"2024-05-26","channelId":1535,"downClick":37,"channelKey":"jinshouzhi","merchantName":"金手指","realInstallTotal":"53"},{"date":"2024-05-26","channelId":1533,"downClick":62,"channelKey":"chunfeng2","merchantName":"春风导航","realInstallTotal":"99"},{"date":"2024-05-26","channelId":1531,"downClick":31,"channelKey":"668","merchantName":"668","realInstallTotal":"35"},{"date":"2024-05-26","channelId":1528,"downClick":8,"channelKey":"yaoji03","merchantName":"妖姬导航","realInstallTotal":"18"},{"date":"2024-05-26","channelId":1523,"downClick":1,"channelKey":"lm09","merchantName":"lm09","realInstallTotal":"1"},{"date":"2024-05-26","channelId":1511,"downClick":34,"channelKey":"lm08","merchantName":"lm08","realInstallTotal":"26"},{"date":"2024-05-26","channelId":1427,"downClick":27,"channelKey":"yulu","merchantName":"雨露","realInstallTotal":"40"},{"date":"2024-05-26","channelId":1421,"downClick":28,"channelKey":"chunshui","merchantName":"春水","realInstallTotal":"29"},{"date":"2024-05-26","channelId":1420,"downClick":30,"channelKey":"tianya","merchantName":"天涯pro","realInstallTotal":"49"},{"date":"2024-05-26","channelId":1417,"downClick":14,"channelKey":"langyou","merchantName":"狼友","realInstallTotal":"14"},{"date":"2024-05-26","channelId":1415,"downClick":20,"channelKey":"sejie","merchantName":"色戒","realInstallTotal":"26"},{"date":"2024-05-26","channelId":1405,"downClick":35,"channelKey":"wuye","merchantName":"午夜","realInstallTotal":"56"},{"date":"2024-05-26","channelId":1400,"downClick":23,"channelKey":"selang","merchantName":"色狼","realInstallTotal":"41"},{"date":"2024-05-26","channelId":1396,"downClick":23,"channelKey":"yt01","merchantName":"一筒导航","realInstallTotal":"46"}],"options":[]},"systemTimestamp":1716660723,"systemDateTime":"2024-05-26 02:12:03","msg":"OK"})
     */
    public function downloadStatistic()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $channelKeyword = [];
            $clickKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['channelKey'])) {
                // 2023-12-25 渠道key改为只有后%
                $channelList = ChannelModel::create()->field(['channelId', 'channelKey'])->where(['channelKey' => [$param['channelKey'] . '%', 'LIKE']])->all();
                if (!$channelList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $channelIdList = array_column($channelList, 'channelId');
                $keyword['c.channelId'] = $channelIdList;
                $channelKeyword['channelId'] = $channelIdList;
                $clickKeyword['channelId'] = $channelIdList;
            }


            isset($param['dateStart']) && $channelKeyword['dateStart'] = $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $channelKeyword['dateEnd'] = $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'cd.date', // 日期
                'cd.channelId', // 渠道id
                'c.channelKey', // 渠道key
                'm.merchantName', // 商户名
                'cd.downClick', // 实际总安装数
            ];

            $sortType = $param['sortType'] ?? '';

            $cis = ChannelDownloadModel::create();
            $data = $cis
                ->alias('cd')
                ->join(ChannelModel::create()->getTableName() . ' AS c', 'cd.channelId = c.channelId', 'LEFT')
                ->join(MerchantModel::create()->getTableName() . ' AS m', 'c.merchantId = m.merchantId', 'LEFT')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);


            $acs = ChannelInstallStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

            // 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
            // 查询分页条件内的日期的关联点击数据
            $acsList = $acs
                ->field([
                    'date',
                    'channelId',
                    'CONCAT(date,\'_\',channelId) AS dateKey',
                    'IFNULL(SUM(realInstallTotal),0) AS realInstallTotal',
                ])
                ->where($where)
                ->group('date, channelId')
                ->indexBy('dateKey');

            foreach ($data['list'] as $datum) {
                $dateChannelKey = $datum['date'] . '_' . $datum['channelId'];
                $datum['realInstallTotal'] = $acsList[$dateChannelKey]['realInstallTotal'] ?? 0;
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '渠道点击下载按钮统计.xlsx';
                } else {
                    $fileName = '渠道点击下载按钮统计.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['渠道id', 'channelId'],
                    ['渠道key', 'channelKey'],
                    ['所属商户', 'merchantName'],
                    ['实际安装数', 'realInstallTotal'],
                    ['点击下载按钮数', 'downClick'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    ##### 商户后台操作 start #####

    /**
     * 我的渠道列表（商户）
     * @Api(name="我的渠道列表（商户）",path="/Api/Admin/Merchant/Channel/myChannelList")
     * @ApiDescription("我的渠道列表（商户）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":9,"list":[{"channelId":9,"channelKey":"yinghua.html","installAndroid":0},{"channelId":8,"channelKey":"sm.html","installAndroid":0},{"channelId":7,"channelKey":"dj.html","installAndroid":0},{"channelId":6,"channelKey":"dongjing888","installAndroid":0},{"channelId":5,"channelKey":"yinhua888","installAndroid":0},{"channelId":4,"channelKey":"123.html","installAndroid":0},{"channelId":3,"channelKey":"lb1","installAndroid":0},{"channelId":2,"channelKey":"a123","installAndroid":0},{"channelId":1,"channelKey":"test123","installAndroid":0}],"options":{"merchantId":1}},"systemTimestamp":1695728804,"systemDateTime":"2023-09-26 19:46:44","msg":"OK"})
     */
    public function myChannelList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['merchantId'] = $this->who['merchantId'];

            $field = ['channelId', 'channelKey'];

            $data = ChannelModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = ChannelInstallStatisticModel::create()->appendInfo(
                $data['list'],
                ['installAndroid'], // 目前只展示今天安装安卓数
                'channelId',
                'channelId',
                ['date' => date('Y-m-d')]
            );

            foreach ($data['list'] as $datum) {
                $datum['installAndroid'] || $datum['installAndroid'] = 0;
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 渠道统计列表（商户）
     * @Api(name="渠道统计列表（商户）",path="/Api/Admin/Merchant/Channel/statisticList")
     * @ApiDescription("渠道统计列表（商户）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["date_DESC", "date_ASC"], description="1.时间倒序（date_DESC）2.时间正序（date_ASC）")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-09-20","channelId":9,"count":239,"channelKey":"yinghua.html"},{"date":"2023-09-20","channelId":7,"count":162,"channelKey":"dj.html"},{"date":"2023-09-20","channelId":1,"count":1,"channelKey":"test123"}],"options":{"dateStart":"2023-09-20","dateEnd":"2023-09-20","countLimit":1},"sum":{"count":"402"}},"systemTimestamp":1695731766,"systemDateTime":"2023-09-26 20:36:06","msg":"OK"})
     */
    public function statisticList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            if (isset($param['channelKey'])) {
                $channel = ChannelModel::create()
                    ->where([
                        'channelKey' => $param['channelKey'],
                        'merchantId' => $this->who['merchantId'],
                    ])
                    ->get();

                if ($channel) {
                    $keyword['channelId'] = $channel->channelId;
                } else {
                    // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                    return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
                }
            } else {
                $channelIdList = ChannelModel::create()
                    ->where(['merchantId' => $this->who['merchantId'], 'status' => ChannelModel::STATE_NORMAL])
                    ->column('channelId');

                $channelIdList && $keyword['channelId'] = $channelIdList;
            }

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            // 2023-09-20 商户看到的一定要是统计数据中大于0的
            $keyword['countLimit'] = 1;

            $field = [
                'date',
                'channelId',
                'installAndroid AS count',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ChannelInstallStatisticModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);
            $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey'], 'channelId', 'channelId');

            $sum = ChannelInstallStatisticModel::create()->getSum($keyword);
            $data['sum'] = ['count' => $sum['installAndroid']];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 渠道安装统计（商户）
     * @Api(name="渠道安装统计（商户）",path="/Api/Admin/Merchant/Channel/installStatistic")
     * @ApiDescription("渠道安装统计（商户）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-09-20","channelId":9,"count":239,"channelKey":"yinghua.html"},{"date":"2023-09-20","channelId":7,"count":162,"channelKey":"dj.html"},{"date":"2023-09-20","channelId":1,"count":1,"channelKey":"test123"}],"options":{"dateStart":"2023-09-20","dateEnd":"2023-09-20","countLimit":1},"sum":{"count":"402"}},"systemTimestamp":1695731766,"systemDateTime":"2023-09-26 20:36:06","msg":"OK"})
     */
    public function installStatistic()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $clickKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $channel = ChannelModel::create()
                ->where([
                    'merchantId' => $this->who['merchantId'],
                ])
                ->get();

            if ($channel) {
                $keyword['channelId'] = $channel->channelId;
                $pageId = PageModel::create()->where(['pageName' => $channel['channelKey']])->val('pageId');
                $pageId && $psKeyword['ps.pageId'] = $clickKeyword['pageId'] = $pageId;

            } else {
                // 如果都不存在这个渠道key那么肯定是没有数据的，直接返回空。
                return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
            }

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));
            // 2023-09-20 商户看到的一定要是统计数据中大于0的

            $field = [
                'date',
                'channelId',
                'installAndroid AS count',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ChannelInstallStatisticModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);
            $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey'], 'channelId', 'channelId');
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageId'], 'channelKey', 'pageName');

            $acs = AdClickStatisticModel::create();
            $where = $acs->parseKeywordToWhere($clickKeyword);

// 因为有分页，查询关联数据也要处理一下查询条件
            $dateList = array_unique(array_column($data['list'], 'date'));
            $dateList && $acs->where(['date' => [$dateList, 'IN']]);
// 查询分页条件内的日期的关联点击数据
            $acsList = $acs
                ->field([
                    'pageId',
                    'date',
                    'CONCAT(date,\'_\',pageId) AS dateKey',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where($where)
                ->group('date, pageId')
                ->indexBy('dateKey');
            $installSum = 0;
            $cpaConfigArray = [];
            $cpaConfigList = ChannelCpaConfigModel::create()->where(['channelId' => $channel->channelId])->all();
            foreach ($cpaConfigList as $item){
                $cpaConfigArray[$item['date'] . '_cpaCost'] = $item['cpaCost'];
                $cpaConfigArray[$item['date'] . '_coefficient'] = $item['coefficient'];
            }
            foreach ($data['list'] as $datum) {
                // 2023-10-14 增加 页面对应的点击总数和 新增点击数/新增安装的比值（新增点击比）
                $clickCountKey = $datum['date'] . '_' . $datum['pageId'];
                $datum['clickCount'] = $acsList[$clickCountKey]['clickCount'] ?? 0;

                $cpaCostKey = $datum['date'] . '_cpaCost';
                $cpaCoefficient = $datum['date'] . '_coefficient';
                if(isset($cpaConfigArray[$cpaCostKey]) && isset($cpaConfigArray[$cpaCoefficient])){
                    $install = $cpaConfigArray[$cpaCostKey] > 0 ? $cpaConfigArray[$cpaCoefficient] * $datum['clickCount'] / $cpaConfigArray[$cpaCostKey] : 0;
                }else{
                    $cpaConfigExisting = ChannelCpaConfigModel::create()
                        ->where(['date' => $datum['date']])
                        ->where(['channelId' => $datum['channelId']])
                        ->get();
                    if(!$cpaConfigExisting){
                        $cpaConfigData = [
                            'date' => $datum['date'],
                            'channelId' => $datum['channelId'],
                            'cpaCost' => $channel->cpaCost,
                            'coefficient' => $channel->coefficient,
                        ];
                        ChannelCpaConfigModel::create($cpaConfigData)->save();
                    }
                    $install = $channel->cpaCost > 0 ? $channel->coefficient * $datum['clickCount'] / $channel->cpaCost : 0;
                }
                $datum['install'] = floor($install);
                $installSum += $datum['install'];
            }
            $data['sum'] = $installSum;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    ##### 商户后台操作 end #####
}