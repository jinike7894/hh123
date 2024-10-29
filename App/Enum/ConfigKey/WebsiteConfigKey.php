<?php

namespace App\Enum\ConfigKey;

class WebsiteConfigKey
{
    const TITLE = 'WebsiteTitle';
    const KEYWORDS = 'WebsiteKeywords';
    const DESCRIPTION = 'WebsiteDescription';
    const CONTACT = 'WebsiteContact';
    const CDN = 'CDN';
    const WEBSITE_STATISTIC_ENABLED = 'WebsiteStatisticEnabled';
    const WEBSITE_STATISTIC_CONFIG = 'WebsiteStatisticConfig';
    const FAVICON = 'Favicon';
    const WEBSITE_CUSTOMER_SERVICE = 'WebsiteCustomerService';
    const WEBSITE_CONTACT_GROUP = 'WebsiteContactGroup';
    const MAIN_ANNOUNCEMENT = 'MainAnnouncement';
    const GAME_NOTIFY = 'GameNotify';
    const App_NOTIFY = 'AppNotify';
    const Recommend_Url = 'RecommendUrl';
    const Spare_Url = 'SpareUrl';
    const E_Mail = 'EMail';
    const Permanent_Url = 'PermanentUrl';
    const Andown = 'AndroidDownloadUrl';
    const Iosdown = 'IOSDownloadUrl';
    const ALL_KEY = [
        self::TITLE,
        self::KEYWORDS,
        self::DESCRIPTION,
        // self::CONTACT,
        self::CDN,
        self::Andown,
        self::Iosdown,
        // 2023-10-26 将统计代码扣量的配置写到了每一个页面中
        //self::WEBSITE_STATISTIC_ENABLED,
        //self::WEBSITE_STATISTIC_CONFIG,
        // self::FAVICON,
        // self::WEBSITE_CUSTOMER_SERVICE,
        // self::WEBSITE_CONTACT_GROUP,
        // self::MAIN_ANNOUNCEMENT,
        // self::GAME_NOTIFY,
        self::App_NOTIFY,
        // self::Recommend_Url,
        // self::Spare_Url,
        // self::E_Mail,
        // self::Permanent_Url,
    ];

}