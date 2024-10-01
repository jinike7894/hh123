<?php

namespace App\RedisKey\Video;

class VideoKey
{
    /**
     * 影视首页
     * @return string
     */
    public static function VideoIndexData()
    {
        return 'Video_VideoIndexData';
    }

    public static function MovePictureKey($id)
    {
        return 'MovePicture_' . $id;
    }

    public static function EncryptionImageKey($id)
    {
        return 'EncryptionVideoImage_' . $id;
    }

    /**
     * 搜索热词
     * @return string
     */
    public static function hotWord()
    {
        return 'Video_hotWord';
    }

    /**
     * 搜索热词
     * @return string
     */
    public static function adultHotWord()
    {
        return 'Video_adultHotWord';
    }
}