<?php

namespace App\RedisKey\Prostitute;

class ProstituteKey
{
    /**
     * 页面访问统计临时ip的hash缓存键
     * @return string
     */
    public static function clickStatTempIpLongHash($date, $deviceId, $prostituteId)
    {
        return 'ProstituteClickStatTempIpLongHash_' . $date . '_' . $deviceId . '_' . $prostituteId;
    }

    public static function EncryptionImageKey($id)
    {
        return 'EncryptionProstituteImage_' . $id;
    }
}