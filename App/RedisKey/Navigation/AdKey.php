<?php

namespace App\RedisKey\Navigation;

class AdKey
{
    /**
     * 页面访问统计临时ip的bitmap缓存键
     * @return string
     */
    public static function clickStatTempIpLongBit($date, $pageId, $adId, $step)
    {
        return 'AdClickStatTempIpLongBit_' . $date . '_' . $pageId . '_' . $adId . '_' . $step;
    }

    /**
     * 页面访问统计临时ip的hash缓存键
     * @return string
     */
    public static function clickStatTempIpLongHash($date, $deviceId, $pageId, $adId)
    {
        return 'AdClickStatTempIpLongHash_' . $date . '_' . $deviceId . '_' . $pageId . '_' . $adId;
    }

    public static function EncryptionImageKey($id)
    {
        return 'EncryptionAdImage_' . $id;
    }
}