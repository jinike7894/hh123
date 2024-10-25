<?php

namespace App\HttpController\Api\Admin\Merchant;

use App\Enum\BalanceChangeType;
use App\Enum\ConfigKey\NavigationConfigKey;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\UserType;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Admin\AdminModel;
use App\Model\Common\ConfigModel;
use App\Model\Merchant\ChannelInstallStatisticModel;
use App\Model\Merchant\MerchantBalanceChangeModel;
use App\Model\Merchant\MerchantClickBillModel;
use App\Model\Merchant\MerchantModel;
use App\Service\Common\BalanceCorrectionService;
use App\Service\Merchant\MerchantService;
use App\Utility\RedisLock;
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
use EasySwoole\ORM\Collection\Collection;
use Exception;
use Throwable;

/**
 * Class Merchant
 * @package App\HttpController\Api\Admin\Merchant
 * @ApiGroup(groupName="后台-商户-商户 Admin/Merchant/Merchant")
 * @ApiGroupDescription("后台商户相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Merchant extends AdminBase
{
    ##### 商户管理 begin #####
    /**
     * 商户列表
     * @Api(name="商户列表",path="/Api/Admin/Merchant/Merchant/merchantList")
     * @ApiDescription("商户列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"merchantId":2,"merchantName":"测试商户2","balance":"0.0000","status":1,"unsettledAmount":0,"realBalance":"0.0000"},{"merchantId":1,"merchantName":"测试商户","balance":"10.0000","status":1,"unsettledAmount":"9.0700","realBalance":"0.9300"}],"options":[]},"systemTimestamp":1687954969,"systemDateTime":"2023-06-28 20:22:49","msg":"OK"})
     */
    public function merchantList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['merchantName']) && $keyword['merchantName'] = $param['merchantName'];

            $field = [
                'merchantId',
                'merchantName',
                'balance',
                'status',
            ];

            $data = MerchantModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

            $merchantIdList = array_column($data['list'], 'merchantId');

            $unsettledAmountList = [];
            if ($merchantIdList) {
                $unsettledAmountList = MerchantClickBillModel::create()->getUnsettledAmount($merchantIdList);
            }

            foreach ($data['list'] as $datum) {
                /**
                 * @var Collection $datum
                 */
                $datum['unsettledAmount'] = isset($unsettledAmountList[$datum['merchantId']]) ? $unsettledAmountList[$datum['merchantId']]['amount'] : 0;
                $datum['realBalance'] = bcsub($datum['balance'], $datum['unsettledAmount'], 4);
                $datum->append(['unsettledAmount', 'realBalance']);
            }


        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 全商户关联列表
     * @Api(name="全商户关联列表",path="/Api/Admin/Merchant/Merchant/merchantListAll")
     * @ApiDescription("全商户关联列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"merchantId":2,"merchantName":"测试商户2"},{"merchantId":1,"merchantName":"测试商户"}],"systemTimestamp":1687937470,"systemDateTime":"2023-06-28 15:31:10","msg":"OK"})
     */
    public function merchantListAll()
    {
        try {
            $data = MerchantModel::create()
                ->field(['merchantId', 'merchantName'])
                ->where([
                    'status' => [MerchantModel::STATE_DELETED, '!='],
                ])
                ->setDefaultOrder()
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function merchantLists()
    {
        try {
            $data = MerchantModel::create()
                ->field(['merchantId', 'merchantName'])
                // ->where([
                //     'status' => [MerchantModel::STATE_DELETED, '!='],
                // ])
                ->setDefaultOrder()
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    /**
     * 商户详情
     * @Api(name="商户详情",path="/Api/Admin/Merchant/Merchant/merchantDetail")
     * @ApiDescription("商户详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="1", description="商户id")
     * @ApiSuccess({"code":200,"result":{"merchantId":1,"merchantName":"测试商户","balance":"0.0000","status":1},"systemTimestamp":1687171925,"systemDateTime":"2023-06-19 18:52:05","msg":"OK"})
     */
    public function merchantDetail()
    {
        $param = $this->request()->getRequestParam();

        try {

            $data = MerchantModel::create()->get($param['merchantId']);
            $data = $data->hidden(['createTime', 'updateTime'])->toRawArray();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 添加商户
     * @Api(name="添加商户",path="/Api/Admin/Merchant/Merchant/add")
     * @ApiDescription("添加商户")
     * @Method(allow=["POST"])
     * @Param(name="merchantName", alias="商户名字", type="string", required="", mbLengthMin="1", description="商户名字")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'merchantName' => $param['merchantName'],
                'status' => intval($param['status']),
            ];

            $result = MerchantService::getInstance()->addMerchant($data);

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
     * 编辑商户
     * @Api(name="编辑商户",path="/Api/Admin/Merchant/Merchant/edit")
     * @ApiDescription("编辑商户")
     * @Method(allow=["POST"])
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="1", description="商户id")
     * @Param(name="merchantName", alias="商户名字", type="string", required="", mbLengthMin="1", description="商户名字")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'merchantName' => $param['merchantName'],
                'status' => intval($param['status']),
            ];

            $merchant = MerchantModel::create()->get($param['merchantId']);

            if (!$merchant) {
                throw new Exception('无效的商户id', Status::CODE_BAD_REQUEST);
            }

            $merchant = MerchantModel::create()
                ->where([
                    'merchantName' => $param['merchantName'],
                    'merchantId' => [$param['merchantId'], '!='],
                    'status' => [MerchantModel::STATE_DELETED, '!='],
                ])
                ->get();

            if ($merchant) {
                throw new Exception('该商户名已存在', Status::CODE_BAD_REQUEST);
            }

            $result = MerchantModel::create()->where(['merchantId' => $param['merchantId']])->update($data);

            // 2023-10-16 同时修改商户对应的管理员账号名字
            AdminModel::create()
                ->where([
                    'merchantId' => $param['merchantId'],
                ])
                ->update([
                    'adminNickname' => $param['merchantName'],
                    'adminAccount' => $param['merchantName'],
                ]);

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
     * 删除商户
     * @Api(name="删除商户",path="/Api/Admin/Merchant/Merchant/delete")
     * @ApiDescription("删除商户")
     * @Method(allow=["POST"])
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="1", description="商户id")
     * @apiSuccess({"code":200,"result":1,"systemTimestamp":1686386747,"systemDateTime":"2023-06-10 16:45:47","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $merchant = MerchantModel::create()->get($param['merchantId']);

            if (!$merchant) {
                throw new Exception('无效的商户id', Status::CODE_BAD_REQUEST);
            }

            $result = $merchant->update(['status' => MerchantModel::STATE_DELETED]);

            // 2023-10-16 同时删除商户对应的管理员账号
            $result = AdminModel::create()
                ->where(['merchantId' => $merchant->merchantId])
                ->update(['status' => AdminModel::STATE_DELETED]);

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

    ##### 商户管理 end #####

    ##### 商户账变管理 begin #####

    /**
     * 商户账变列表
     * @Api(name="商户账变列表",path="/Api/Admin/Merchant/Merchant/balanceChangeList")
     * @ApiDescription("商户账变列表，商户id和商户名字同时只能有一个，id会覆盖名字")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="merchantId", alias="商户id", type="int", optional="", min="1", description="商户id")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="type", alias="账变类型", type="string", optional="", mbLengthMin="1", description="账变类型 BalanceChangeType")
     * @Param(name="createDateStart", alias="订单创建开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="createDateEnd", alias="订单创建结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @ApiSuccess({"code":200,"result":{"total":4,"list":[{"merchantBalanceChangeId":4,"merchantId":2,"type":"ManualAdd","amount":"10.0000","preBalance":"0.0000","newBalance":"10.0000","remark":"管理员[tommy]操作。测试备注","createTime":"2023-06-26 19:49:41","updateTime":"2023-06-26 20:27:06","merchantName":"测试商户2","typeName":"调整加币"},{"merchantBalanceChangeId":3,"merchantId":2,"type":"ManualReduce","amount":"-5.0000","preBalance":"5.0000","newBalance":"0.0000","remark":"管理员[tommy]操作。","createTime":"2023-06-25 19:44:05","updateTime":"2023-06-26 20:27:08","merchantName":"测试商户2","typeName":"调整减币"},{"merchantBalanceChangeId":2,"merchantId":1,"type":"ManualReduce","amount":"-5.0000","preBalance":"10.0000","newBalance":"5.0000","remark":"管理员[tommy]操作。","createTime":"2023-06-24 19:44:05","updateTime":"2023-06-26 19:54:50","merchantName":"测试商户","typeName":"调整减币"},{"merchantBalanceChangeId":1,"merchantId":1,"type":"ManualAdd","amount":"10.0000","preBalance":"0.0000","newBalance":"10.0000","remark":"管理员[tommy]操作。","createTime":"2023-06-23 19:44:05","updateTime":"2023-06-26 19:54:48","merchantName":"测试商户","typeName":"调整加币"}],"options":[]},"systemTimestamp":1687783740,"systemDateTime":"2023-06-26 20:49:00","msg":"OK"})
     */
    public function balanceChangeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            if (isset($param['merchantName'])) {
                $merchant = MerchantModel::create()->where(['merchantName' => $param['merchantName']])->get();
                $merchant && $keyword['mbc.merchantId'] = $merchant->merchantId;
            }
            isset($param['type']) && $keyword['type'] = $param['type'];
            isset($param['merchantId']) && $keyword['mbc.merchantId'] = $param['merchantId'];
            isset($param['createDateStart']) && $keyword['createDateStart'] = date('Y-m-d', strtotime($param['createDateStart']));
            isset($param['createDateEnd']) && $keyword['createDateEnd'] = date('Y-m-d', strtotime($param['createDateEnd']));

            $field = [
                'mbc.merchantBalanceChangeId',
                'mbc.merchantId',
                'm.merchantName',
                'mbc.type',
                'mbc.amount',
                'mbc.preBalance',
                'mbc.newBalance',
                'mbc.remark',
                'mbc.createTime',
                'mbc.updateTime',
            ];

            $data = MerchantBalanceChangeModel::create()
                ->alias('mbc')
                ->join(MerchantModel::create()->getTableName() . ' AS m', 'mbc.merchantId = m.merchantId')
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

            foreach ($data['list'] as $datum) {
                $datum['typeName'] = BalanceChangeType::TYPE_ALL_TEXT[$datum['type']];
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 手动调整余额
     * @Api(name="手动调整余额",path="/Api/Admin/Merchant/Merchant/manualChangeBalance")
     * @ApiDescription("手动调整余额")
     * @Method(allow=["POST"])
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="1", description="商户id")
     * @Param(name="amount", alias="币数量", type="float", required="", max="100000000", description="币数量，正数增加负数减少")
     * @Param(name="remark", alias="备注", type="string", required="", description="备注，可以传空字符串")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1687779486,"systemDateTime":"2023-06-26 19:38:06","msg":"OK"})
     */
    public function manualChangeBalance()
    {
        $param = $this->request()->getRequestParam();

        try {
            RedisLock::timeLimitLock($this->jwt . __CLASS__ . '\\' . __FUNCTION__);

            $param['remark'] = '管理员[' . $this->who['adminAccount'] . ']操作。' . ($param['remark'] ?? '');

            $param['whoId'] = $param['merchantId'];
            $param['whoType'] = UserType::TYPE_MERCHANT;

            $result = BalanceCorrectionService::getInstance()->correct($param);
        } catch (\Throwable $e) {
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

    ##### 商户账变管理 end #####

    /**
     * 商户余额提醒
     * @Api(name="商户余额提醒",path="/Api/Admin/Merchant/Merchant/balanceReminder")
     * @ApiDescription("商户余额提醒，返回了余额不足的商户总数和具体的商户名与余额。")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"merchantId":1,"merchantName":"测试商户","balance":"10.0000","unsettledAmount":"9.0700","realBalance":"0.9300"},{"merchantId":2,"merchantName":"测试商户2","balance":"0.0000","unsettledAmount":0,"realBalance":"0.0000"}]},"systemTimestamp":1687956712,"systemDateTime":"2023-06-28 20:51:52","msg":"OK"})
     */
    public function balanceReminder()
    {
        try {
            RedisLock::timeLimitLock($this->jwt . __CLASS__ . '\\' . __FUNCTION__);

            $config = ConfigModel::create()->getConfigValueList(NavigationConfigKey::ALL_KEY);

            if (!$config['MerchantBalanceReminder']) {
                throw new Exception('商户余额提醒未开启', Status::CODE_BAD_REQUEST);
            }

            $merchantList = MerchantModel::create()
                ->field(['merchantId', 'merchantName', 'balance'])
                ->where(['status' => MerchantModel::STATE_NORMAL])
                ->all();

            $merchantIdList = array_column($merchantList, 'merchantId');
            $unsettledAmountList = [];
            if ($merchantIdList) {
                $unsettledAmountList = MerchantClickBillModel::create()->getUnsettledAmount($merchantIdList);
            }

            $result = ['total' => 0, 'list' => []];
            foreach ($merchantList as $datum) {
                /**
                 * @var Collection $datum
                 */
                $datum['unsettledAmount'] = isset($unsettledAmountList[$datum['merchantId']]) ? $unsettledAmountList[$datum['merchantId']]['amount'] : 0;
                $datum['realBalance'] = bcsub($datum['balance'], $datum['unsettledAmount'], 4);
                $datum->append(['unsettledAmount', 'realBalance']);

                if ($datum['realBalance'] <= $config['ReminderAmount']) {
                    $result['list'][] = $datum;
                }
            }

            $result['total'] = count($result['list']);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    ##### 商户后台操作 start #####

    /**
     * 商户仪表盘（商户）
     * @Api(name="商户仪表盘（商户）",path="/Api/Admin/Merchant/Merchant/dashboard")
     * @ApiDescription("商户仪表盘（商户）")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"balance":"0.0000","channelInstallCount":"6","channelInstallActive":"8"},"systemTimestamp":1693560400,"systemDateTime":"2023-09-01 17:26:40","msg":"OK"})
     */
    /*public function dashboard()
    {
        $param = $this->request()->getRequestParam();

        try {
            $merchant = MerchantModel::create()->get($this->who['merchantId']);
            $date = date('Y-m-d');
            $channelDateCount = ChannelInstallStatisticModel::create()->getTotalByDate($date, $merchant->merchantId);

            $result = [
                'balance' => $merchant->balance,
                'channelInstallCount' => $channelDateCount['count'],
                'channelInstallActive' => $channelDateCount['active'],
            ];

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }*/
    ##### 商户后台操作 end #####
}