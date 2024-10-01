<?php

namespace App\Enum\ConfigKey;

class AppConfigKey
{
    const ANDROID_VERSION = 'AndroidVersion';
    const ANDROID_MIN_VERSION = 'AndroidMinVersion';
    const ANDROID_DOWNLOAD_URL = 'AndroidDownloadUrl';
    const IOS_VERSION = 'IOSVersion';
    const IOS_MIN_VERSION = 'IOSMinVersion';
    const IOS_DOWNLOAD_URL = 'IOSDownloadUrl';
    const API_DOMAIN = 'ApiDomain';
    const DOWNLOAD_PAGE_URL = 'DownloadPageUrl';
    const H5_PAGE_URL = 'H5PageUrl';
    const AI_VIDEO_PLAY_URL = 'AiVideoPlayUrl';
    const ALL_KEY = [
        self::ANDROID_VERSION,
        self::ANDROID_MIN_VERSION,
        self::ANDROID_DOWNLOAD_URL,
        self::IOS_VERSION,
        self::IOS_MIN_VERSION,
        self::IOS_DOWNLOAD_URL,
        self::API_DOMAIN,
        self::DOWNLOAD_PAGE_URL,
        self::H5_PAGE_URL,
        self::AI_VIDEO_PLAY_URL,
    ];
}