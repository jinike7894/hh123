<?php

namespace App\RedisKey\Market;

class VipOrderKey
{
    public static function createVipOrderLock($userId)
    {
        return 'VipOrder:Lock_CreateVipOrder_' . $userId;
    }

    public static function lock($orderId)
    {
        return 'VipOrder:Lock_VipOrder_' . $orderId;
    }
}