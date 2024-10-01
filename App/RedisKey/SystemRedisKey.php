<?php

namespace App\RedisKey;

class SystemRedisKey
{
    /**
     * 登录session
     * @param $userId
     * @param $userType
     * @return string
     */
    public static function session($userId, $userType)
    {
        return "Session_{$userType}_{$userId}";
    }

    public static function loginFailureLimit($userId, $userType)
    {
        return "SessionLimit_{$userType}_{$userId}";
    }
    public static function sessionCounts($userId, $userType)
    {
        return "SessionCounts_{$userType}_{$userId}";
    }
}