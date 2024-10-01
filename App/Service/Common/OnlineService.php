<?php

namespace App\Service\Common;

use App\Enum\RedisDb;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\RedisPool;

class OnlineService
{
    use Singleton;

    public $prefix = 'Online:';
    public $redisDb = RedisDb::REDIS_DB_SESSION;
    public $aliveTime = 300;

    /**
     * 记录或刷新在线状态
     * @param $key
     * @param string $group
     * @throws \EasySwoole\Redis\Exception\RedisException
     */
    public function record($key, $group = '')
    {
        $key = $this->createKey($key, $group);
        $redis = RedisPool::defer($this->redisDb);
        $result = $redis->set($key, 1, ['EX' => $this->aliveTime]);
        if (!$result) {
            throw new \Exception('redis记录在线状态失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }
        return true;
    }

    /**
     * @param $key
     * @param string $group
     */
    public function offline($key, $group = '')
    {
        $key = $this->createKey($key, $group);
        $redis = RedisPool::defer($this->redisDb);
        $redis->del($key);
        return true;
    }

    public function isOnline($key, $group = '')
    {
        $key = $this->createKey($key, $group);
        $redis = RedisPool::defer($this->redisDb);
        return $redis->exists($key);
    }

    /**
     * 获取一个组下面的所有key
     * @param $group
     * @return array
     */
    public function getGroupKeys($group)
    {
        $regex = $this->createScanReg($group);
        $matchKeys = [];
        $cursor = 0;
        $redis = RedisPool::defer($this->redisDb);

        do {
            $keys = $redis->scan($cursor, $regex);
            $matchKeys = array_merge($matchKeys, $keys);
        } while ($cursor > 0);

        return array_unique($matchKeys);
    }

    /**
     * 获取一个组下面的所有原始key
     * @param $group
     * @return array
     */
    public function getGroupOriginalKey($group)
    {
        $matchKeys = $this->getGroupKeys($group);
        $matchKeys = array_map(function ($item) use ($group) {
            return str_replace($this->prefix . $group . ':', '', $item);
        }, $matchKeys);
        return $matchKeys;
    }

    /**
     * 创建扫描用的正则
     * @param $group
     * @return string
     */
    public function createScanReg($group)
    {
        return $this->prefix . $group . ':*';
    }

    /**
     * 创建存储key
     * @param $key
     * @param string $group
     * @return string
     */
    public function createKey($key, $group = '')
    {
        $group || $group = 'default';
        return $this->prefix . $group . ':' . $key;
    }
}