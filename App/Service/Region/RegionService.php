<?php

namespace App\Service\Region;

use App\Model\Region\ProvinceModel;
use App\RedisKey\Region\RegionKey;
use EasySwoole\Component\Singleton;
use EasySwoole\RedisPool\RedisPool;

class RegionService
{
    use Singleton;

    /**
     * 获取省份和市级的列表
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getProvinceCityList()
    {
        $key = RegionKey::provinceCityKey();

        $redis = RedisPool::defer();
        $data = $redis->get($key);

        if (!$data) {
            $data = ProvinceModel::create()
                ->with('cityRelation')
                ->all();

            $redis->setEx($key, 600, $data);
        }

        return $data;
    }
}