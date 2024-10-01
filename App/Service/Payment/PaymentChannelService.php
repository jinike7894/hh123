<?php

namespace App\Service\Payment;

use App\Model\Payment\PaymentChannelModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class PaymentChannelService
{
    use Singleton;

    public function addPaymentChannel($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $channelId = PaymentChannelModel::create($data)->save();

            if (!$channelId) {
                throw new Exception('支付渠道添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $channelId;
    }

    public function editPaymentChannel($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $paymentChannel = PaymentChannelModel::create()->get([
                'paymentChannelId' => $data['paymentChannelId'],
                'status' => [PaymentChannelModel::STATE_DELETED, '>'],
            ]);

            if (!$paymentChannel) {
                throw new Exception('无效的支付渠道id', Status::CODE_BAD_REQUEST);
            }

            $result = $paymentChannel->update($data);

            if ($result === false) {
                throw new Exception('支付渠道修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}