<?php

namespace App\Service\Payment;

use App\Model\Payment\PaymentChannelModel;
use App\Model\Payment\PaymentTypeModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class PaymentTypeService
{
    use Singleton;

    public function editPaymentType($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $paymentType = PaymentTypeModel::create()->get([
                'paymentTypeId' => $data['paymentTypeId'],
                'status' => [PaymentTypeModel::STATE_DELETED, '>'],
            ]);

            if (!$paymentType) {
                throw new Exception('无效的支付类型id', Status::CODE_BAD_REQUEST);
            }

            $result = $paymentType->update($data);

            if ($result === false) {
                throw new Exception('支付类型修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}