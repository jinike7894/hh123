<?php

namespace App\Model\Payment;

use App\Model\BaseModel;

class PaymentWhiteListModel extends BaseModel
{
    protected $tableName = 'payment_white_list';

    protected $primaryKey = 'paymentWhiteId';

    public function isExists($payName, $ipLong)
    {
        return !!$this
            ->where([
                'platformObj' => $payName,
                'ipLong' => $ipLong,
            ])
            ->val('paymentWhiteId');
    }
}