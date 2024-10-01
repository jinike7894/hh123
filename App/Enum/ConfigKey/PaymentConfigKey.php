<?php

namespace App\Enum\ConfigKey;

class PaymentConfigKey
{
    const ALIPAY_WAY_CODE = 'AlipayWayCode';
    const WECHAT_WAY_CODE = 'WechatWayCode';

    const ALL_KEY = [
        self::ALIPAY_WAY_CODE,
        self::WECHAT_WAY_CODE,
    ];
}