<?php

namespace App\RedisKey\Merchant;

class MerchantKey
{
    public static function manualChangeBalance($merchantId)
    {
        return 'manualChangeBalance_' . $merchantId;
    }
}