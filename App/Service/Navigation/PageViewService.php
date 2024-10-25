<?php

namespace App\Service\Navigation;

use App\Enum\RedisDb;
use App\Model\Navigation\LandPageStatisticModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageStatisticModel;
use App\RedisKey\Navigation\PageKey;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class PageViewService
{
    use Singleton;

    public function getStatistic($pageId, $date = '')
    {
        $date || $date = date('Y-m-d');

        $redis = RedisPool::defer();
        $pvKey = PageKey::statisticPv($date, $pageId);
        $ipKey = PageKey::statisticIp($date, $pageId);

        list($pv, $ip) = $redis->mGet([$pvKey, $ipKey]);

        if ($pv && $ip) {
            $data = [
                'date' => $date,
                'pageId' => $pageId,
                'pv' => $pv,
                'ip' => $ip,
            ];
        } else {
            $data = PageStatisticModel::create()
                ->where([
                    'pageId' => $pageId,
                    'date' => $date
                ])
                ->get();

            if ($data) {
                $redis->set($pvKey, $data['pv'], 600);
                $redis->set($ipKey, $data['ip'], 600);
            } else {
                $data = [
                    'date' => $date,
                    'pageId' => $pageId,
                    'pv' => 0,
                    'ip' => 0,
                ];
            }
        }

        return $data;
    }

    /**
     * 记录访问统计
     * 解释一下业务
     * 这个是系统内部的对应各页面（渠道）的pv ip 扣量ip 统计。
     * 扣量ip因为要与三方统计保持一致，则需要在前端判断是否要扣量，而不是在服务端判断，这是为了兼容纯静态项目中的统计方式。
     * @param $param
     */
    public function recordView($param)
    {
        // TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var PageModel $page
                 */
                $page = &$param['page'];
                $pageId = $page->pageId;
                $ipLong = ip2long($param['ip']);
                $date = &$param['date'];
                $ipState = &$param['ipState'];

                $isCounted = $this->getStatisticTemporaryIpHash($date, $pageId, $ipLong);

                // 如果已经被统计过了，则只增加pv
                $duplicate = ['pv' => QueryBuilder::inc()];
                if (!$isCounted) {
                    // 如果未被统计过，则增加pv ip，以及记录redis
                    $duplicate['ip'] = QueryBuilder::inc();

                    // 2023-10-30增加扣量ip的统计
                    if ($ipState) {
                        $duplicate['reducedIp'] = QueryBuilder::inc();
                    }

                    $this->setStatisticTemporaryIpHash($date, $pageId, $ipLong, 1);
                }
                try {
                    DbManager::getInstance()->startTransactionWithCount();
                    $pageStatic=PageStatisticModel::create()->where(["pageId"=>$pageId,'date' => $date])->lockForUpdate()->get();
                    file_put_contents('test.json', json_encode($pageStatic)."\r\n",FILE_APPEND);
                    if($pageStatic){
                        //更新
                        $updatePage=[
                            "pv"=>$pageStatic->pv+1,
                        ];
                        if (!$isCounted) {
                            $updatePage["ip"]=$pageStatic->ip+1;
                            if ($ipState) {
                                $updatePage['reducedIp'] = $pageStatic->reducedIp+1;
                            }
        
                        }
                        PageStatisticModel::create()->update($updatePage,["pageId"=>$pageId,'date' => $date]);
                    }else{
                        //添加
                        PageStatisticModel::create(['pageId' => $pageId, 'date' => $date, 'pv' => 1, 'ip' => 1, 'reducedIp' => $ipState])->save();
                    }
                    DbManager::getInstance()->commitWithCount();
                } catch (Throwable  $e) {
                    DbManager::getInstance()->rollbackWithCount();
                    throw new Exception($e->getMessage(), $e->getCode());
                }
               
                // PageStatisticModel::create()
                //     ->data(['pageId' => $pageId, 'date' => $date, 'pv' => 1, 'ip' => 1, 'reducedIp' => $ipState])
                //     ->duplicate($duplicate)
                //     ->save();

                // 写了数据库后要更新缓存
                $redis = RedisPool::defer();
                $pvKey = PageKey::statisticPv($date, $pageId);
                $redis->incr($pvKey);
                $redis->expire($pvKey, 600);
                if (!$isCounted) {
                    $ipKey = PageKey::statisticIp($date, $pageId);
                    $redis->incr($ipKey);
                    $redis->expire($ipKey, 600);
                }

            } catch (Throwable  $e) {
                //file_put_contents('take-error.log',date('Y-m-d H:i:s',time()).'-recordView-'.json_encode($param).'-error:'.$e->getMessage()."\r\n",FILE_APPEND);
                throw new Exception($e->getMessage(), $e->getCode());
            }
        // });
    }

    /**
     * 记录落地页访问统计
     * 解释一下业务
     * 这个是落地页导航统计，给运营看的，不扣量
     * @param $param
     */
    public function landPageView($param)
    {
        TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var PageModel $page
                 */
                $channelKey = $param['channelKey'];
                $date = &$param['date'];

                // 如果未被统计过，则增加pv ip，以及记录redis
                $duplicate['ip'] = QueryBuilder::inc();

                LandPageStatisticModel::create()
                    ->data(['channelKey' => $channelKey, 'date' => $date, 'ip' => 1])
                    ->duplicate($duplicate)
                    ->save();

            } catch (Throwable  $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        });
    }

    /**
     * 记录落地页点击跳转统计
     * 解释一下业务
     * 这个是落地页导航统计，给运营看的，不扣量
     * @param $param
     */
    public function landPageClick($param)
    {
        TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var PageModel $page
                 */
                $channelKey = $param['channelKey'];
                $date = &$param['date'];

                // 如果未被统计过，则增加pv ip，以及记录redis
                $duplicate['click'] = QueryBuilder::inc();

                LandPageStatisticModel::create()
                    ->data(['channelKey' => $channelKey, 'date' => $date, 'click' => 1])
                    ->duplicate($duplicate)
                    ->save();
            } catch (Throwable  $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        });
    }


    /**
     * 记录h5一些页面访问统计
     * @param $param
     */
    public function h5PageView($param)
    {
        TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var PageModel $page
                 */
                $channelKey = $param['channelKey'];
                $date = &$param['date'];
                $name = $param['name'];

                // 如果未被统计过，则增加pv ip，以及记录redis
                $duplicate[$name] = QueryBuilder::inc();

                LandPageStatisticModel::create()
                    ->data(['channelKey' => $channelKey, 'date' => $date])
                    ->duplicate($duplicate)
                    ->save();
            } catch (Throwable  $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }
        });
    }

    public function getStatisticTemporaryIpBit($date, $pageId, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        list($left, $right) = Func::splitIpLong($ipLong);
        $key = PageKey::statisticTempIpLongBit($date, $pageId, $left);
        return $redis->getBit($key, $right);
    }

    public function setStatisticTemporaryIpBit($date, $pageId, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        list($left, $right) = Func::splitIpLong($ipLong);
        $key = PageKey::statisticTempIpLongBit($date, $pageId, $left);
        $result = $redis->setBit($key, $right, $value);
        $time = Func::getExpireTime($pageId);
        $redis->expire($key, $time);
        return $result;
    }

    public function getStatisticTemporaryIpHash($date, $pageId, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticTempIpLongHash($date, $pageId);
        return $redis->hGet($key, $ipLong);
    }

    public function setStatisticTemporaryIpHash($date, $pageId, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticTempIpLongHash($date, $pageId);
        $result = $redis->hSet($key, $ipLong, $value);
        $time = Func::getExpireTime($pageId);
        $redis->expire($key, $time);
        return $result;
    }

    public function getLandPageStatisticTemporaryIpHash($date, $channelKey, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticTempIpLongHash($date, $channelKey);
        return $redis->hGet($key, $ipLong);
    }

    public function setLandPageStatisticTemporaryIpHash($date, $channelKey, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticTempIpLongHash($date, $channelKey);
        $result = $redis->hSet($key, $ipLong, $value);
        $time = Func::getExpireTime($channelKey);
        $redis->expire($key, $time);
        return $result;
    }


    public function getLandPageClickStatisticTemporaryIpHash($date, $channelKey, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticClickTempIpLongHash($date, $channelKey);
        return $redis->hGet($key, $ipLong);
    }

    public function setLandPageClickStatisticTemporaryIpHash($date, $channelKey, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticClickTempIpLongHash($date, $channelKey);
        $result = $redis->hSet($key, $ipLong, $value);
        $time = Func::getExpireTime($channelKey);
        $redis->expire($key, $time);
        return $result;
    }


    public function getH5Statistic($name, $date, $channelKey, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = $name.'H5StatisticTempIpLongHash_' . $date . '_' . $channelKey;
        return $redis->hGet($key, $ipLong);
    }

    public function setH5Statistic($name, $date, $channelKey, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = $name.'H5StatisticTempIpLongHash_' . $date . '_' . $channelKey;
        $result = $redis->hSet($key, $ipLong, $value);
        $time = Func::getExpireTime($channelKey);
        $redis->expire($key, $time);
        return $result;
    }
}