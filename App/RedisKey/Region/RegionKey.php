<?php

namespace App\RedisKey\Region;

class RegionKey
{
    /**
     * 省份和市级的列表Key
     */
    public static function provinceCityKey()
    {
        return 'Region_ProvinceCityList';
    }
}