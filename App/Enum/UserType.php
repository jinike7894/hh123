<?php

namespace App\Enum;

class UserType
{
    const TYPE_MEMBER = 'Member';
    const TYPE_MERCHANT = 'Merchant';
    const TYPE_SYSTEM = 'System';

    const ALL_TYPE = [self::TYPE_MEMBER, self::TYPE_MERCHANT, self::TYPE_SYSTEM];

    const TYPE_NAME_LIST = [
        self::TYPE_MEMBER => '用户',
        self::TYPE_MERCHANT => '商户',
        self::TYPE_SYSTEM => '系统',
    ];
}