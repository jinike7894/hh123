<?php

namespace App\RedisKey\Navigation;

class AdGroupKey
{
    public static function extension($adGroupId)
    {
        return 'adGroupExtensionFields_' . $adGroupId;
    }
}