<?php

namespace App\Model\Common;

use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\RedisPool\RedisPool;
use Exception;
/**
 * Class ConfigModel
 * @package App\Model\Common
 * @property $configId int | id
 * @property $configGroup enum | 配置分组
 * @property $cfgKey string | 配置项键
 * @property $cfgValue string | 配置项值
 * @property $title string | 配置项标题
 * @property $description string | 配置项描述
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ConfigModel extends AbstractModel
{
    protected $tableName = 'config';

    protected $primaryKey = 'configId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    /**
     * 配置分组
     * 测试 Test
     * 系统 System
     * 网站 WebSite
     * 应用 App
     */
    const GROUP_TEST = 'Test';
    const GROUP_SYSTEM = 'System';
    const GROUP_WEBSITE = 'WebSite';
    const GROUP_APP = 'App';
    const GROUP_OSS = 'Oss';
    const GROUP_JSMS = 'JSMS';

    /**
     * 获取配置
     * @param string $configKey
     * @return mixed
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getConfigValue(string $configKey)
    {
        $redis = RedisPool::defer();
        $cfgValue = $redis->get($configKey);

        if ($cfgValue === false) {
            throw new \Exception('redis操作失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        if ($cfgValue === null) {
            $cfgValue = $this->where(['cfgKey' => $configKey])->val('cfgValue');

            if ($cfgValue === null) {
                throw new \Exception("缺少配置项[{$configKey}]", Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $result = $redis->setEx($configKey, 600, $cfgValue);

            if ($result === false) {
                throw new \Exception('redis操作失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
        }

        return $cfgValue;
    }

    /**
     * 批量获取配置
     * @param array $configKey
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getConfigValueList(array $configKey)
    {
        $redis = RedisPool::defer();
        $cacheData = $redis->mGet($configKey);

        if ($cacheData === false) {
            throw new \Exception('redis操作失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        $combineData = array_combine($configKey, $cacheData);

        $nullKey = [];
        foreach ($combineData as $key => $combineDatum) {
            if ($combineDatum === null) {
                $nullKey[] = $key;
            }
        }

        if ($nullKey) {
            $queryData = $this->field(['cfgKey', 'cfgValue'])
                ->where([
                    'cfgKey' => [$nullKey, 'IN']
                ])
                ->all();

            if ($queryData) {
                $queryData = array_column($queryData, 'cfgValue', 'cfgKey');
                $combineData = array_merge($combineData, $queryData);
            }
        }

        foreach ($combineData as $key => $combineDatum) {
            if ($combineDatum === null) {
                throw new \Exception("缺少配置项[{$key}]", Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $result = $redis->setEx($key, 600, $combineDatum);
            if ($result === false) {
                throw new \Exception('redis操作失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
        }

        return $combineData;
    }

    /**
     * 批量设置配置
     * @param array $data
     * @return int 影响行数
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function setConfig(array $data)
    {
        $count = 0;
        $redis = RedisPool::defer();

        foreach ($data as $key => $datum) {
            $result = $this->where(['cfgKey' => $key])->update(['cfgValue' => $datum]);
            if ($result) {
                $redis->setEx($key, 600, $datum);
                $count++;
            };
        }

        return $count;
    }

    /**
     * 根据分组获取配置
     * @param string $group
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getConfigValueByGroup(string $group)
    {
        $keyList = $this->where(['configGroup' => $group])->column('cfgKey');
        return $keyList;
        return $this->getConfigValueList($keyList);
    }
}