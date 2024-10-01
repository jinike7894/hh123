<?php

namespace App\Utility;

use App\Enum\RedisDb;
use EasySwoole\Component\Timer;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\RedisPool;
use Swoole\Coroutine;

class RedisLock
{

    /**
     * 添加看门狗来防止因A锁过期，从而让B获取到锁
     * 释放锁添加了唯一value值判断来防止A删B锁
     * 以上两个操作都是在为了确保锁的唯一性
     *
     * 用 set NX EX 或 PX 是为了保证操作的原子性，必须一次性成功，不能分开操作，避免死锁。
     *
     * 只有在传了超时时间 timeout 的情况下才会返回 false，否则会一直等待到获取锁为止。
     * @param string $key
     * @param int $timeout
     * @param int $ttl
     * @return false|string
     * @throws \EasySwoole\Redis\Exception\RedisException
     */
    public static function lock(string $key, int $timeout = 0, int $ttl = 30)
    {
        $uniqueValue = uniqid();
        // 加锁需要一个不会被手动清除的库
        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $result = $redis->set($key, $uniqueValue, ['NX', 'EX' => $ttl]);

        if (!$result) {
            $timeoutPoint = microtime(true) + $timeout;

            do {
                Coroutine::sleep(0.2);

                if ($timeout > 0 && microtime(true) > $timeoutPoint) {
                    return false;
                }

                $result = $redis->set($key, $uniqueValue, ['NX', 'EX' => $ttl]);
                if ($result) {
                    break;
                }

            } while (true);
        }

        self::watchDog($key, $uniqueValue, $ttl);

        return $uniqueValue;
    }

    /**
     * 释放锁
     * @param string $key 锁键值
     * @param string $value 加锁时返回的唯一值
     * @return false|string
     */
    public static function releaseLock(string $key, string $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $data = $redis->get($key);
        if ($data && ($value == $data)) {
            return $redis->del($key);
        }

        return false;
    }

    /**
     * 添加看门狗是为了防止A锁超时导致B获取到锁从而没有达到唯一性
     * @param string $key
     * @param string $value
     * @param int $ttl
     */
    public static function watchDog(string $key, string $value, int $ttl)
    {
        $delay = ceil($ttl / 2);
        Timer::getInstance()->after($delay * 1000, function ($key, $value, $ttl) {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
            $data = $redis->get($key);
            if ($data == $value) {
                $redis->expire($key, $ttl);
                // 再次调用自己达到免超时效果
                RedisLock::watchDog($key, $value, $ttl);
            }
        }, $key, $value, $ttl);
    }


    ##### 下面开始是一些自定义的锁操作 #####

    /**
     * 频率时限锁，强制时限内仅操作一次。
     * @param string $key
     * @param int $ttl
     * @return bool
     * @throws \EasySwoole\Redis\Exception\RedisException
     */
    public static function timeLimitLock(string $key, int $ttl = 3)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $result = $redis->set($key, 1, ['NX', 'EX' => $ttl]);

        if (!$result) {
            throw new \Exception('请勿频繁操作。', Status::CODE_BAD_REQUEST);
        }

        return $result;
    }
}