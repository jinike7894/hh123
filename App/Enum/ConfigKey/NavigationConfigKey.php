<?php

namespace App\Enum\ConfigKey;

class NavigationConfigKey
{
    // 商户余额提醒开关 是否开启余额不足提醒 1.是 0.否
    const MERCHANT_BALANCE_REMINDER = 'MerchantBalanceReminder';
    // 商户余额提醒金额 低于这个数就会提醒
    const REMINDER_AMOUNT = 'ReminderAmount';
    // 商户余额提醒频率 单位秒
    const REMINDER_FREQUENCY = 'ReminderFrequency';
    // 单个IP广告点击次数统计限制 0为不限制
    const SINGLE_IP_DAILY_CLICK_LIMIT = 'SingleIpDailyClickLimit';

    const ALL_KEY = [
        self::MERCHANT_BALANCE_REMINDER,
        self::REMINDER_AMOUNT,
        self::REMINDER_FREQUENCY,
        self::SINGLE_IP_DAILY_CLICK_LIMIT,
    ];
}