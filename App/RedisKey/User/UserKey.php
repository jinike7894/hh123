<?php

namespace App\RedisKey\User;

class UserKey
{
    public static function deviceCreateAccount($ipLong, $date)
    {
        return 'DeviceCreateAccount_' . $date . '_' . $ipLong;
    }
}