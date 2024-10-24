<?php

namespace App\Service\Market;

use App\Model\Merchant\ChannelModel;
use App\Model\Navigation\PageModel;
use App\Model\Payment\PaymentChannelModel;
use App\Model\User\UserModel;
use App\Model\User\UserVipGoodsModel;
use App\Model\User\UserVipOrderModel;
use App\RedisKey\Market\VipOrderKey;
use App\RedisKey\Payment\PaymentKey;
use App\Service\Payment\PaymentService;
use App\Service\User\UserService;
use App\Utility\RedisLock;
use EasySwoole\Component\Singleton;
use EasySwoole\Component\Timer;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class VipOrderService
{
    use Singleton;

    /**
     * 创建订单
     * $data = [
     * 'userId' => $this->who['userId'],
     * 'goodsId' => intval($param['goodsId']),
     * 'paymentChannelId' => intval($param['paymentChannelId']),
     * 'pageName' => $pageName,
     * 'channelKey' => $channelKey,
     * 'ip' => $ip,
     * ];
     * @param $data
     * @return mixed
     * @throws Throwable
     */
    public function createVipOrder($data)
    {
        try {
            $lockKey = VipOrderKey::createVipOrderLock($data['userId']);
            $lockValue = RedisLock::lock($lockKey);

            DbManager::getInstance()->startTransactionWithCount();

            $user = UserModel::create()->get($data['userId']);
            if ($user->status != UserModel::STATE_NORMAL) {
                throw new Exception('账号异常', Status::CODE_BAD_REQUEST);
            }
            // 充值前必须绑定手机号成为正式用户
            /*if (empty($user->phoneNumber)) {
                throw new Exception('请先绑定手机号', Status::CODE_BAD_REQUEST);
            }*/

            // 检查商品是否还在
            $vipGoods = UserVipGoodsModel::create()->get($data['goodsId']);
            if ($vipGoods->status != UserVipGoodsModel::STATE_NORMAL) {
                throw new Exception('商品不可用', Status::CODE_BAD_REQUEST);
            }

            // 做进行中单数限制
            $progressOrderCount = UserVipOrderModel::create()->where([
                'userId' => $user->userId,
                'status' => UserVipOrderModel::STATE_WAITING_BUYERS_PAYMENT,
            ])->count();
            if ($progressOrderCount >= 100) {
                throw new Exception('当前您未完成的订单过多，暂不能发起新订单。', Status::CODE_BAD_REQUEST);
            }

            // 这个是记录h5还是app的来源，傻逼玩意儿想一出是一出。
            // 是h5就有pageId，是app就有channelId，反正总要有一个。
            if (isset($data['pageName']) && $data['pageName']) {
                $pageId = PageModel::create()->where(['pageName' => $data['pageName']])->val('pageId');
                $pageId || $pageId = 0;
            } else {
                $pageId = 0;
            }

            if (isset($data['channelKey']) && $data['channelKey']) {
                $channelId = ChannelModel::create()->where(['channelKey' => $data['channelKey']])->val('channelId');
                $channelId || $channelId = 0;
            } else {
                $channelId = 0;
            }

            if (!$pageId && !$channelId) {
                throw new Exception('无效的渠道参数', Status::CODE_BAD_REQUEST);
            }

            // 拿支付渠道和关联的渠道类型和渠道平台
            $paymentChannel = PaymentChannelModel::create()
                ->with(['platformRelation'])
                ->get($data['paymentChannelId']);
            if (!$paymentChannel) {
                throw new Exception('无效的支付渠道参数', Status::CODE_BAD_REQUEST);
            }
            $userVipOrder = UserVipOrderModel::create([
                'userId' => $data['userId'],
                'pageId' => $pageId,
                'channelId' => $channelId,
                'paymentTypeId' => $paymentChannel->paymentTypeId,
                'paymentChannelId' => $paymentChannel->paymentChannelId,
                'goodsId' => $data['goodsId'],
                'amount' => $vipGoods->goodsPresentPrice,
                'status' => UserVipOrderModel::STATE_WAITING_BUYERS_PAYMENT,
                'createDate' => date('Y-m-d'),
            ]);
            $result = $userVipOrder->save();
            if (!$result) {
                throw new Exception('创建订单记录失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            /* 订单创建完成后就可以去拿支付订单了 begin */
            $class = $paymentChannel['platformRelation']['platformObj'];
            $paymentInstance = PaymentService::getInstance()->load($class);
            $paymentResult = $paymentInstance->createOrder([
                'paymentChannel' => $paymentChannel,
                'goodsName' => $vipGoods->goodsName,
                'orderNo' => $userVipOrder->orderNo,
                'amount' => $userVipOrder->amount,
                'ip' => $data['ip'],
            ]);
            $paymentResult['orderId'] = $userVipOrder->orderId;
            $paymentResult['orderNo'] = $userVipOrder->orderNo;

            /* 订单创建完成后就可以去拿支付订单了 end */

            DbManager::getInstance()->commitWithCount();
            $diff = $paymentResult['expiredTime'] - time();
            // 一个小时10单可以吧，免得有些支付支付时间比较长，提前过期了整的不能回调。
            $delayTime = $diff > 3600 ? $diff : 3600;

            // 创建延时任务自动过期
            $orderNo = $userVipOrder->orderNo;
            Timer::getInstance()->after($delayTime * 1000, function () use ($orderNo) {
                $this->buyerPaymentTimeout($orderNo);
            });

            RedisLock::releaseLock($lockKey, $lockValue);
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            RedisLock::releaseLock($lockKey, $lockValue);
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $paymentResult;
    }

    /**
     * VIP订单超时
     * @param $orderNo
     * @return bool
     * @throws Throwable
     */
    public function buyerPaymentTimeout($orderNo)
    {
        try {
            $lockKey = PaymentKey::lock($orderNo);
            $lockValue = RedisLock::lock($lockKey);

            DbManager::getInstance()->startTransactionWithCount();

            $vipOrder = UserVipOrderModel::create()
                ->lockForUpdate()
                ->get(['orderNo' => $orderNo]);
            if (!$vipOrder) {
                throw new Exception('没有对应的订单号', Status::CODE_BAD_REQUEST);
            }

            if ($vipOrder->status != UserVipOrderModel::STATE_WAITING_BUYERS_PAYMENT) {
                throw new Exception('订单状态不符', Status::CODE_BAD_REQUEST);
            }

            $result = $vipOrder->update(['status' => UserVipOrderModel::STATE_BUYER_PAYMENT_TIMEOUT]);
            if (!$result) {
                throw new Exception('订单状态修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();

            RedisLock::releaseLock($lockKey, $lockValue);
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            RedisLock::releaseLock($lockKey, $lockValue);
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    ##### 订单回调处理 begin #####
    public function handleOrderCallback($orderNo)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $vipOrder = UserVipOrderModel::create()
                ->lockForUpdate()
                ->get(['orderNo' => $orderNo]);
            if (!$vipOrder) {
                throw new Exception('没有对应的订单号', Status::CODE_BAD_REQUEST);
            }

            if ($vipOrder->status != UserVipOrderModel::STATE_WAITING_BUYERS_PAYMENT) {
                throw new Exception('订单状态不符', Status::CODE_BAD_REQUEST);
            }

            $now = time();
            $nowDate = date('Y-m-d', $now);
            $result = $vipOrder->update([
                'status' => UserVipOrderModel::STATE_ORDER_COMPLETED,
                'finishDate' => $nowDate,
                'finishTime' => date('Y-m-d H:i:s', $now),
            ]);
            if (!$result) {
                throw new Exception('VIP订单数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $vipGoods = UserVipGoodsModel::create()->get($vipOrder['goodsId']);
            $aiType = ['AiFaceImg','AiFaceVideo','AiPicture'];
            if(in_array($vipGoods['goodsType'],$aiType)){
                UserService::getInstance()->increaseAiTimes($vipOrder['userId'], $vipGoods['days'], $vipGoods['goodsType']);
            }else{
                UserService::getInstance()->increaseVIPDays($vipOrder['userId'], $vipGoods['days'], $nowDate);
            }
            DbManager::getInstance()->commitWithCount();

        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    ##### 订单回调处理 end #####

    /**
     * 手动完成订单（是后台单独调用的）
     * @param $orderNo
     * @return bool
     * @throws Throwable
     */
    public function completeOrder($orderNo)
    {
        try {
            $lockKey = PaymentKey::lock($orderNo);
            $lockValue = RedisLock::lock($lockKey);

            DbManager::getInstance()->startTransactionWithCount();

            $vipOrder = UserVipOrderModel::create()
                ->lockForUpdate()
                ->get(['orderNo' => $orderNo]);
            if (!$vipOrder) {
                throw new Exception('没有对应的订单号', Status::CODE_BAD_REQUEST);
            }

            if ($vipOrder->status == UserVipOrderModel::STATE_ORDER_COMPLETED) {
                throw new Exception('订单状态不符', Status::CODE_BAD_REQUEST);
            }

            $now = time();
            $nowDate = date('Y-m-d', $now);
            $result = $vipOrder->update([
                'status' => UserVipOrderModel::STATE_ORDER_COMPLETED,
                'finishDate' => $nowDate,
                'finishTime' => date('Y-m-d H:i:s', $now),
            ]);
            if (!$result) {
                throw new Exception('VIP订单数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $vipGoods = UserVipGoodsModel::create()->get($vipOrder['goodsId']);
            UserService::getInstance()->increaseVIPDays($vipOrder['userId'], $vipGoods['days'], $nowDate);

            DbManager::getInstance()->commitWithCount();

            RedisLock::releaseLock($lockKey, $lockValue);
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            RedisLock::releaseLock($lockKey, $lockValue);
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 手动退款订单并封号
     * @param $orderNo
     * @return bool
     * @throws Throwable
     */
    public function refundOrder($orderNo)
    {
        try {
            $lockKey = PaymentKey::lock($orderNo);
            $lockValue = RedisLock::lock($lockKey);

            DbManager::getInstance()->startTransactionWithCount();

            $vipOrder = UserVipOrderModel::create()
                ->lockForUpdate()
                ->get(['orderNo' => $orderNo]);
            if (!$vipOrder) {
                throw new Exception('没有对应的订单号', Status::CODE_BAD_REQUEST);
            }

            if ($vipOrder->status != UserVipOrderModel::STATE_ORDER_COMPLETED) {
                throw new Exception('订单状态不符', Status::CODE_BAD_REQUEST);
            }


            $result = $vipOrder->update([
                'status' => UserVipOrderModel::STATE_BUYER_CANCELS_PAYMENT,
            ]);
            if (!$result) {
                throw new Exception('VIP订单数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $user = UserModel::create()->get(['userId' => $vipOrder->userId]);

            $result = $user->update(['status' => UserModel::STATE_FORBIDDEN]);
            if (!$result) {
                throw new Exception('用户数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();

            RedisLock::releaseLock($lockKey, $lockValue);
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            RedisLock::releaseLock($lockKey, $lockValue);
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}