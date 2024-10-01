<?php

namespace App\Service\Prostitute;

use App\Enum\RedisDb;
use App\Model\Prostitute\ProstituteClickModel;
use App\Model\Prostitute\ProstituteModel;
use App\Model\Prostitute\ProstitutePictureModel;
use App\Model\Prostitute\ProstituteTypeModel;
use App\RedisKey\Navigation\AdKey;
use App\RedisKey\Prostitute\ProstituteKey;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

class ProstituteService
{
    use Singleton;

    public function addProstitute($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $prostituteId = ProstituteModel::create($data)->save();

            if ($prostituteId === false) {
                throw new Exception('楼凤添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            if (count($data['pictureList']) == count($data['pictureSort'])) {
                $this->saveProstitutePictureList($prostituteId, $data['fileType'], $data['pictureList'], $data['pictureSort']);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $prostituteId;
    }

    public function editProstitute($data, $withPic = true)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $prostitute = ProstituteModel::create()->get([
                'prostituteId' => $data['prostituteId'],
                'status' => [ProstituteModel::STATE_DELETED, '>'],
            ]);

            if (!$prostitute) {
                throw new Exception('无效的楼凤id', Status::CODE_BAD_REQUEST);
            }

            $result = $prostitute->update($data);

            if ($result === false) {
                throw new Exception('楼凤修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            if ($withPic) {
                // 不管怎么样，编辑都要先删除之前的图片
                ProstitutePictureModel::create()->where(['prostituteId' => $prostitute->prostituteId])->destroy();

                if (isset($data['pictureList']) && isset($data['pictureSort']) && (count($data['pictureList']) == count($data['pictureSort']))) {
                    $this->saveProstitutePictureList($prostitute->prostituteId, $data['fileType'], $data['pictureList'], $data['pictureSort']);
                }
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 保存楼凤图片列表
     * @param $prostituteId
     * @param $fileType
     * @param $picList
     * @param $sortList
     * @throws Throwable
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function saveProstitutePictureList($prostituteId, $fileType, $picList, $sortList)
    {
        foreach ($picList as $key => $picture) {
            ProstitutePictureModel::create([
                'prostituteId' => $prostituteId,
                'fileType' => $fileType,
                'url' => $picture,
                'url2' => $picture,
                'sort' => $sortList[$key] ?? 0,
            ])->save();
        }
    }

    public function getDetail($keyword, $withType = true, $withRegion = true)
    {
        if ($withRegion) {
            $prostitute = ProstituteModel::create()
                ->with(['prostitutePictureRelation', 'provinceRelation', 'cityRelation'])
                ->setKeyWord($keyword)
                ->get();
        } else {
            $prostitute = ProstituteModel::create()
                ->with(['prostitutePictureRelation'])
                ->setKeyWord($keyword)
                ->get();
        }


        $data = $prostitute->toRawArray();
        $data['extension'] = json_decode($data['extension'], true);
        $data['prostitutePictureRelation'] = $prostitute['prostitutePictureRelation'];

        if ($withRegion) {
            $data['provinceRelation'] = $prostitute['provinceRelation'];
            $data['cityRelation'] = $prostitute['cityRelation'];
        }

        if ($withType) {
            $prostituteType = ProstituteTypeModel::create()
                ->field(['prostituteTypeId', 'title', 'typeKey', 'relatedAdId', 'sort', 'status'])
                ->get($prostitute['prostituteTypeId']);
            $data['prostituteType'] = $prostituteType;
        }

        return $data;
    }

    public function click($param)
    {
        try {
            $time = time();
            $ipLong = ip2long($param['ip']);
            $date = date('Y-m-d', $time);

            DbManager::getInstance()->startTransactionWithCount();

            $prostitute = ProstituteModel::create()->get([
                'prostituteId' => $param['prostituteId'],
                'type' => ProstituteModel::TYPE_REAL,
                'status' => [ProstituteModel::STATE_DELETED, '>'],
            ]);

            if (!$prostitute) {
                throw new Exception('无效的楼凤id', Status::CODE_BAD_REQUEST);
            }

            // 这里的点击去重逻辑用设备id和ip
            $isCounted = $this->getStatisticTemporaryIpHash($date, $param['deviceId'], $prostitute->prostituteId, $ipLong);

            if (!$isCounted) {
                $duplicate = ['clickCount' => QueryBuilder::inc()];

                $this->setStatisticTemporaryIpHash($date, $param['deviceId'], $prostitute->prostituteId, $ipLong, 1);

                ProstituteClickModel::create()
                    ->data([
                        'date' => $date,
                        'prostituteId' => $prostitute->prostituteId,
                        'contact' => $prostitute->contact,
                        'clickCount' => 1,
                    ])
                    ->duplicate($duplicate)
                    ->save();
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * ### 注意 ###
     * 这样写是不同设备同一个ip也会单独计算为一个，会让该ip的点击计数+1，如果想要不区分设备，就按照ip来区分，则需要在这里把get和set方法中的
     * 获取key的方法中的deviceId去掉才可以哈。
     * 意思就是 将 AdKey::clickStatTempIpLongHash 方法的去掉deviceId
     * @param $date
     * @param $deviceId
     * @param $prostituteId
     * @param $ipLong
     * @return false|string
     */
    public function getStatisticTemporaryIpHash($date, $deviceId, $prostituteId, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = ProstituteKey::clickStatTempIpLongHash($date, $deviceId, $prostituteId);
        return $redis->hGet($key, $ipLong);
    }

    public function setStatisticTemporaryIpHash($date, $deviceId, $prostituteId, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = ProstituteKey::clickStatTempIpLongHash($date, $deviceId, $prostituteId);
        $result = $redis->hIncrBy($key, $ipLong, $value);
        $time = Func::getExpireTime($prostituteId);
        $redis->expire($key, $time);
        return $result;
    }

    /**
     * 批量修改联系方式
     * @param $adIdList
     * @param $data
     * @return bool
     * @throws Throwable
     */
    public function batchEditAd($prostituteIdList, $data): bool
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            if (!$prostituteIdList) {
                throw new Exception('楼凤id参数错误', Status::CODE_BAD_REQUEST);
            }

            $result = ProstituteModel::create()
                ->where(['prostituteId' => [$prostituteIdList, 'IN']])
                ->update($data);

            if ($result === false) {
                throw new Exception('批量修改联系方式出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

}