<?php

namespace App\HttpController\Api\Market;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\User\UserVipGoodsModel;
use App\Model\User\UserVipOrderModel;
use App\Service\Market\VipOrderService;
use App\Service\User\UserService;
use App\Utility\RedisLock;
use EasySwoole\EasySwoole\Core;
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
 * Class Vip
 * @package App\HttpController\Api\Market
 * @ApiGroup(groupName="市场-Vip Market/Vip")
 * @ApiGroupDescription("市场-Vip相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Vip extends UserBase
{
    /**
     * vip订单列表
     * @Api(name="vip订单列表",path="/Api/Market/Vip/getVipOrderList")
     * @ApiDescription("vip订单列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="status", alias="状态", type="string", optional="", inArray=["WaitingBuyersPayment", "BuyerCancelsPayment", "BuyerPaymentTimeout", "OrderCompleted"], description="WaitingBuyersPayment 等待买家付款, BuyerCancelsPayment 买家取消付款, BuyerPaymentTimeout 买家付款超时, OrderCompleted 订单完成")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"orderId":14,"orderNo":"VIP202312051758350014","userId":100001,"paymentTypeId":1,"paymentChannelId":2,"goodsId":3,"amount":"200.00","status":"WaitingBuyersPayment","createDate":"2023-12-05","createTime":"2023-12-05 17:58:35","finishTime":"1000-01-01 00:00:00","goodsName":"至尊永久卡","statusText":"等待买家付款"},{"orderId":11,"orderNo":"VIP202312051747320011","userId":100001,"paymentTypeId":2,"paymentChannelId":6,"goodsId":3,"amount":"200.00","status":"OrderCompleted","createDate":"2023-12-05","createTime":"2023-12-05 17:47:32","finishTime":"2023-12-05 17:48:55","goodsName":"至尊永久卡","statusText":"订单完成"}],"options":[]},"systemTimestamp":1701838615,"systemDateTime":"2023-12-06 12:56:55","msg":"OK"})
     */
    public function getVipOrderList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));
            isset($param['status']) && $keyword['status'] = trim($param['status']);
            $keyword['userId'] = $this->who['userId'];

            $field = [
                'orderId',
                'orderNo',
                'userId',
                'paymentTypeId',
                'paymentChannelId',
                'goodsId',
                'amount',
                'status',
                'createDate',
                'createTime',
                'finishTime',
            ];

            $data = UserVipOrderModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);
            $data['list'] = UserVipGoodsModel::create()->appendInfo($data['list'], ['goodsName'], 'goodsId', 'goodsId');

            foreach ($data['list'] as $datum) {
                $datum->statusText = UserVipOrderModel::STATUS_NAME_LIST[$datum->status];
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 创建开通VIP订单
     * @Api(name="创建开通VIP订单",path="/Api/Market/Vip/createVipOrder")
     * @ApiDescription("创建开通VIP订单")
     * @Method(allow=["POST"])
     * @Param(name="goodsId", alias="商品id", type="int", required="", min="1", description="商品id")
     * @Param(name="paymentChannelId", alias="支付渠道id", type="int", required="", min="1", description="支付渠道id")
     * @Param(name="pageName", alias="页面名", type="string", optional="", mbLengthMin="1", description="H5的来源传这个")
     * @Param(name="channelKey", alias="页面名", type="string", optional="", mbLengthMin="1", description="App的来源传这个")
     * @apiSuccess()
     */
    public function createVipOrder()
    {
        $param = $this->request()->getRequestParam();

        try {
            $pageName = trim($param['pageName'] ?? '');
            $channelKey = trim($param['channelKey'] ?? '');

            if (empty($pageName) && empty($channelKey)) {
                throw new Exception('未知的渠道参数', Status::CODE_BAD_REQUEST);
            }
            $ip = $this->clientRealIP();
            // 这个是测试用的
            if (Core::getInstance()->runMode() == 'dev' && isset($param['ip'])) {
                $ip = $param['ip'];
            }
            RedisLock::timeLimitLock($this->jwt . __CLASS__ . '\\' . __FUNCTION__);
            $data = [
                'userId' => $this->who['userId'],
                'goodsId' => intval($param['goodsId']),
                'paymentChannelId' => intval($param['paymentChannelId']),
                'pageName' => $pageName,
                'channelKey' => $channelKey,
                'ip' => $ip,
            ];

            $result = VipOrderService::getInstance()->createVipOrder($data);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * vip订单状态列表
     * @Api(name="vip订单状态列表",path="/Api/Market/Vip/getVipOrderStatus")
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
     * 赠送vip会员
     * @Api(name="赠送vip会员",path="/Api/Market/Vip/giftUserVipDays")
     * @ApiDescription("赠送vip会员")
     * @Method(allow=["GET", "POST"])
     * @Param(name="userId", alias="用户id", type="string", required="", mbLengthMin="1", description="用户id")
     * @Param(name="days", alias="vip天数", type="string", required="", mbLengthMin="1", description="vip天数")
     * @ApiSuccess({"code":200,"result":false,"systemTimestamp":1706078291,"systemDateTime":"2024-01-24 14:38:11","msg":"OK"})
     */
    public function giftUserVipDays()
    {
        $param = $this->request()->getRequestParam();
        $userId = trim($param['userId'] ?? '');
        $days = trim($param['days'] ?? '');
        try {
            $result = false;
            if ($userId) {
                $inviter = UserModel::create()->lockForUpdate()->get($userId);
                if ($inviter) {
                    $result = UserService::getInstance()->increaseVIPDays($inviter, $days);
                }
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

}