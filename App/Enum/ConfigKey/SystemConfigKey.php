<?php

namespace App\Enum\ConfigKey;

/**
 * 系统配置key
 * Class SystemConfigKey
 * @package App\Enum\ConfigKey
 */
class SystemConfigKey
{

    const PAGE_SIZE = 20;

    // 前台页面入口目录
    const FRONTEND_PATH = EASYSWOOLE_ROOT . '/dist';

    ###### 验证码 begin ######
    const VERIFY_CODE_TTL = 300; // 图形验证码过期时间
    const VERIFY_CODE_LENGTH = 4; // 图形验证码长度
    const VERIFY_SMS_CODE_TTL = 300; // 短信验证码过期时间
    const VERIFY_SMS_CODE_LENGTH = 4; // 短信验证码长度
    const VERIFY_SMS_CODE_TIME_LIMIT = 60; // 连续发送时间间隔秒数
    ###### 验证码 end ######

    // 平台维护
    const WEBSITE_MAINTENANCE = 'WebsiteMaintenance';

    // 强制单设备登录
    const FORCE_SINGLE_DEVICE_LOGIN = 'ForceSingleDeviceLogin';


}
