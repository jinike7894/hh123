<?php

namespace App\RedisKey\Video;

class ShortVideoKey
{
    /**
     * 搜索热词
     * @return string
     */
    public static function hotWord()
    {
        return 'ShortVideo_hotWord';
    }

    public static function shortVideoLike($userId, $vodId)
    {
        return 'ShortVideoLike_' . $userId . '_' . $vodId;
    }

    public static function EncryptionImageKey($id)
    {
        return 'EncryptionShortVideoImage_' . $id;
    }
}