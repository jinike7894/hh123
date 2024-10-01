<?php

namespace App\HttpController\Api\Admin\Market;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\UserType;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Merchant\ChannelModel;
use App\Model\Navigation\PageModel;
use App\Model\Payment\PaymentChannelModel;
use App\Model\Payment\PaymentPlatformModel;
use App\Model\Payment\PaymentTypeModel;
use App\Model\User\UserModel;
use App\Model\User\UserVipGoodsModel;
use App\Model\User\UserVipOrderModel;
use App\Service\Market\CommonOrderService;
use App\Service\Market\VipOrderService;
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
use Exception;
use Throwable;

/**
 * Class VipOrder
 * @package App\HttpController\Api\Admin\Market
 * @ApiGroup(groupName="后台-用户-VIP订单 Admin/Market/VipOrder")
 * @ApiGroupDescription("后台VIP订单相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class VipOrder extends AdminBase
{
    /**
     * vip订单列表
     * @Api(name="vip订单列表",path="/Api/Admin/Market/VipOrder/vipOrderList")
     * @ApiDescription("vip订单列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="userId", alias="用户id", type="int", optional="", min="1", description="用户id")
     * @Param(name="orderId", alias="vip订单id", type="int", optional="", min="1", description="vip订单id")
     * @Param(name="orderNo", alias="vip订单编号", type="string", optional="", mbLengthMin="1", description="vip订单编号")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["orderId_DESC", "orderId_ASC"], description="1.订单id（orderId）")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="status", alias="状态", type="string", optional="", inArray=["WaitingBuyersPayment", "BuyerCancelsPayment", "BuyerPaymentTimeout", "OrderCompleted"], description="WaitingBuyersPayment 等待买家付款, BuyerCancelsPayment 买家取消付款, BuyerPaymentTimeout 买家付款超时, OrderCompleted 订单完成")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"orderId":14,"orderNo":"VIP202312051758350014","userId":100001,"pageId":0,"channelId":5,"paymentTypeId":1,"paymentChannelId":2,"goodsId":3,"amount":"200.00","status":"BuyerPaymentTimeout","createTime":"2023-12-05 17:58:35","finishTime":"1000-01-01 00:00:00","goodsName":"年卡","nickname":"会员6538CDA650221","channelName":"支付宝超级原生","typeName":"支付宝","pageName":"","channelKey":"yinhua888","statusText":"买家付款超时","source":"App"},{"orderId":11,"orderNo":"VIP202312051747320011","userId":100001,"pageId":1,"channelId":0,"paymentTypeId":2,"paymentChannelId":6,"goodsId":3,"amount":"200.00","status":"OrderCompleted","createTime":"2023-12-05 17:47:32","finishTime":"2023-12-05 17:48:55","goodsName":"年卡","nickname":"会员6538CDA650221","channelName":"微信超级原生","typeName":"微信","pageName":"index.html","channelKey":"","statusText":"订单完成","source":"H5"}],"options":[]},"systemTimestamp":1702051582,"systemDateTime":"2023-12-09 00:06:22","msg":"OK"})
     */
    public function vipOrderList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            isset($param['userId']) && $keyword['userId'] = $param['userId'];
            isset($param['orderId']) && $keyword['orderId'] = $param['orderId'];
            isset($param['orderNo']) && $keyword['orderNo'] = trim($param['orderNo']);
            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));
            isset($param['status']) && $keyword['status'] = trim($param['status']);

            if (isset($param['pageName'])) {
                $pageId = PageModel::create()->where(['pageName' => $param['pageName']])->val('pageId');
                $pageId && $keyword['pageId'] = $pageId;
            }
            if (isset($param['channelKey'])) {
                $channelId = ChannelModel::create()->where(['channelKey' => $param['channelKey']])->val('channelId');
                $channelId && $keyword['channelId'] = $channelId;
            }

            $field = [
                'orderId',
                'orderNo',
                'userId',
                'pageId',
                'channelId',
                'paymentTypeId',
                'paymentChannelId',
                'goodsId',
                'amount',
                'status',
                'createTime',
                'finishTime',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = UserVipOrderModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = UserVipGoodsModel::create()->appendInfo($data['list'], ['goodsName'], 'goodsId', 'goodsId');
            $data['list'] = UserModel::create()->appendInfo($data['list'], ['nickname'], 'userId', 'userId');
            $data['list'] = PaymentChannelModel::create()->appendInfo($data['list'], ['channelName', 'paymentPlatformId'], 'paymentChannelId', 'paymentChannelId');
            $data['list'] = PaymentTypeModel::create()->appendInfo($data['list'], ['typeName'], 'paymentTypeId', 'paymentTypeId');
            $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageName'], 'pageId', 'pageId');
            $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['channelKey'], 'channelId', 'channelId');
            $data['list'] = PaymentPlatformModel::create()->appendInfo($data['list'], ['platformName'], 'paymentPlatformId', 'paymentPlatformId');

            foreach ($data['list'] as $datum) {
                $datum->statusText = UserVipOrderModel::STATUS_NAME_LIST[$datum->status];
                $datum->source = $datum->pageId > 0 ? 'H5' : 'App';
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '订单数据.xlsx';
                } else {
                    $fileName = '订单数据.xlsx';
                }
                $headers = [
                    ['通道名称', 'channelName'],
                    ['商品名称', 'goodsName'],
                    ['页面名字', 'pageName'],
                    ['支付平台名称', 'platformName'],
                    ['订单状态', 'statusText'],
                    ['订单类型', 'typeName'],
                    ['来源', 'source'],
                    ['订单金额', 'amount'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * vip订单状态列表
     * @Api(name="vip订单状态列表",path="/Api/Admin/Market/VipOrder/getVipOrderStatus")
     * @ApiDescription("vip订单状态列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"key":"WaitingBuyersPayment","name":"等待买家付款"},{"key":"BuyerCancelsPayment","name":"买家取消付款"},{"key":"BuyerPaymentTimeout","name":"买家付款超时"},{"key":"OrderCompleted","name":"订单完成"}],"systemTimestamp":1701780855,"systemDateTime":"2023-12-05 20:54:15","msg":"OK"})
     */
    public function getVipOrderStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = UserVipOrderModel::STATUS_NAME_KEY_LIST;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 手动完成订单
     * @Api(name="手动完成订单",path="/Api/Admin/Market/VipOrder/completeOrder")
     * @ApiDescription("手动完成订单")
     * @Method(allow=["POST"])
     * @Param(name="orderNo", alias="vip订单编号", type="string", required="", mbLengthMin="1", description="vip订单编号")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function completeOrder()
    {
        $param = $this->request()->getRequestParam();

        try {
            if ($this->who['adminType'] != UserType::TYPE_SYSTEM) {
                throw new Exception('只有管理员可以操作', Status::CODE_FORBIDDEN);
            }

            $result = VipOrderService::getInstance()->completeOrder($param['orderNo']);

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
     * 手动退款订单并封号
     * @Api(name="手动退款订单并封号",path="/Api/Admin/Market/VipOrder/refundOrder")
     * @ApiDescription("手动退款订单并封号")
     * @Method(allow=["POST"])
     * @Param(name="orderNo", alias="vip订单编号", type="string", required="", mbLengthMin="1", description="vip订单编号")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function refundOrder()
    {
        $param = $this->request()->getRequestParam();

        try {
            if ($this->who['adminType'] != UserType::TYPE_SYSTEM) {
                throw new Exception('只有管理员可以操作', Status::CODE_FORBIDDEN);
            }

            $result = VipOrderService::getInstance()->refundOrder($param['orderNo']);

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
}