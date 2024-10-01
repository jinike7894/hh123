<?php

namespace App\RedisKey\Payment;

class PaymentKey
{
    /**
     * 订单锁
     * @param $orderId
     * @return string
     */
    public static function lock($orderNo)
    {
        return 'Lock_Payment_' . $orderNo;
    }

    public static function platform($platformObj)
    {
        return 'Payment_platform_' . $platformObj;
    }
}