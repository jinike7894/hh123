<?php

namespace App\Enum;

/**
 * REDIS 选择数据库
 */
class RedisDb
{
    // db 0 的不需要在获取时传入参数
    // 因为在defer的时候默认参数是 default 不是0
    // 配置文件中直接将默认 db 配置为 0 就可以了
    const REDIS_DB_DEFAULT = 0; // 默认
    const REDIS_DB_AUTH = 1; // 权限
    const REDIS_DB_SESSION = 2; // 登录那些相关的东西，不要瞎清除的
    const REDIS_DB_STATISTIC = 3; // 统计
    const REDIS_DB_QUEUE = 4; // redis队列
    const REDIS_DB_VERIFY_CODE = 5; // 验证码
}