<?php

namespace App\Enum;

class Upload
{
    const TYPE_AD = 'ad';
    const TYPE_ARTICLE = 'article';
    const TYPE_TEMP = 'temp';
    const TYPE_OTHER = 'other';
    const TYPE_VIDEO = 'video';
    const TYPE_PHOTO = 'photo';
    const TYPE_LIVE = 'live';
    const TYPE_AI = 'ai';

    const IMAGE_PATH = 'Upload' . DIRECTORY_SEPARATOR . 'Image' . DIRECTORY_SEPARATOR;

    /**
     * 获取图片日期路径
     * @param string $type
     * @param string $date
     * @return string
     */
    public static function getImageDatePath($type = '', $date = ''): string
    {
        if ($date) {
            $dateTimestamp = is_int($date) ? $date : strtotime($date);
            $datePath = DIRECTORY_SEPARATOR . date('Y', $dateTimestamp) . DIRECTORY_SEPARATOR . date('m', $dateTimestamp) . DIRECTORY_SEPARATOR . date('d', $dateTimestamp);
        } else {
            $datePath = DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d');
        }

        switch ($type) {
            case Upload::TYPE_AD:
            case Upload::TYPE_ARTICLE:
                // ad 目录 要用 article 这是做广告的传统了哈，不然会被屏蔽。
                $path = Upload::IMAGE_PATH . 'article' . $datePath;
                break;
                // 这下面的是用类型名字做目录的
            case Upload::TYPE_VIDEO:
            case Upload::TYPE_PHOTO:
            case Upload::TYPE_TEMP:
            case Upload::TYPE_AI:
            case Upload::TYPE_LIVE:
                $path = Upload::IMAGE_PATH . $type . $datePath;
                break;
            default:
                $path = Upload::IMAGE_PATH . 'other' . $datePath;
        }

        return $path;
    }
}