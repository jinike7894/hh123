<?php

namespace App\RedisKey\Live;

class LiveKey
{
    public static function EncryptionImageKey($id)
    {
        return 'EncryptionLiveImage_' . $id;
    }
}