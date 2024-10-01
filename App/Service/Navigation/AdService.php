<?php

namespace App\Service\Navigation;

use App\Enum\ConfigKey\NavigationConfigKey;
use App\Enum\RedisDb;
use App\Enum\UserType;
use App\Model\Common\ConfigModel;
use App\Model\Merchant\ChannelInstallModel;
use App\Model\Navigation\AdClickRecordModel;
use App\Model\Navigation\AdClickStatisticModel;
use App\Model\Navigation\AdGroupRelationModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\PageModel;
use App\RedisKey\Navigation\AdKey;
use App\Service\Merchant\MerchantService;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

class AdService
{
    use Singleton;

    /**
     * 添加广告
     * @param $ad
     * @param $adGroupIdList
     * @param $adGroupSort
     * @throws Throwable
     */
    public function addAd($ad, $adGroupIdList, $adGroupSort)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $adId = AdModel::create($ad)->save();
            if (!$adId) {
                throw new Exception('广告添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $relationList = [];
            foreach ($adGroupIdList as $adGroupId) {
                $relationList[] = [
                    'adId' => $adId,
                    'adGroupId' => $adGroupId,
                    'sort' => $adGroupSort[$adGroupId] ?? 100,
                ];
            }

            Func::insertAll(AdGroupRelationModel::create(), $relationList);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $adId;
    }

    /**
     * 修改广告
     * @param $ad
     * @param $adGroupIdList
     * @param $adGroupSort
     * @return bool
     * @throws Throwable
     */
    public function editAd($ad, $adGroupIdList, $adGroupSort): bool
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $adId = $ad['adId'];

            AdModel::create()->where(['adId' => $adId])->update($ad);

            /* 广告与广告组的关联 begin */
            AdGroupRelationModel::create()->where(['adId' => $adId])->destroy();

            $relationList = [];
            foreach ($adGroupIdList as $adGroupId) {
                $relationList[] = [
                    'adId' => $adId,
                    'adGroupId' => $adGroupId,
                    'sort' => $adGroupSort[$adGroupId] ?? 100,
                ];
            }

            Func::insertAll(AdGroupRelationModel::create(), $relationList);
            /* 广告与广告组的关联 end */

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 批量修改广告
     * @param $adIdList
     * @param $data
     * @return bool
     * @throws Throwable
     */
    public function batchEditAd($adIdList, $data): bool
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            if (!$adIdList) {
                throw new Exception('广告id参数错误', Status::CODE_BAD_REQUEST);
            }

            $result = AdModel::create()
                ->where(['adId' => [$adIdList, 'IN']])
                ->update($data);

            if ($result === false) {
                throw new Exception('批量修改广告出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 记录点击统计
     * @param $param
     */
    public function recordClick($param)
    {
        TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var AdModel $ad
                 * @var PageModel $page
                 */
                $time = time();
                $ad = &$param['ad'];
                $page = &$param['page'];
                $ipLong = ip2long($param['ip']);
                $date = date('Y-m-d', $time);
                $dateTime = date('Y-m-d H:i:s', $time);

                $isCounted = $this->getStatisticTemporaryIpHash($date, $param['deviceId'], $page->pageId, $ad->adId, $ipLong);

                /* 2023-11-13 增加单个ip单广告每日统计的点击次数限制 begin */
                $letsRecordStatistic = true;
                if ($isCounted) {
                    $limit = ConfigModel::create()->getConfigValue(NavigationConfigKey::SINGLE_IP_DAILY_CLICK_LIMIT);
                    if ($isCounted >= $limit) {
                        $letsRecordStatistic = false;
                    }
                }
                /* 2023-11-13 增加单个ip单广告每日统计的点击次数限制 end */

                // 如果已经被统计过了，则只增加点击数
                $duplicate = ['clickCount' => QueryBuilder::inc()];
                if (!$isCounted) {
                    // 未被统计过的则要数据库要增加
                    $duplicate['clickIpCount'] = QueryBuilder::inc();
                }
                $this->setStatisticTemporaryIpHash($date, $param['deviceId'], $page->pageId, $ad->adId, $ipLong, 1);

                DbManager::getInstance()->startTransactionWithCount();

                if ($letsRecordStatistic) {
                    $recordCost = false;
                    if ($ad->cost > 0 && $ad->merchantId > 0) {
                        // 当费用大于0时且绑定了商户id
                        // 1.需要判断商户余额是否还够扣款的
                        // 2.如果可以扣款就记录和结算扣款
                        // 3.当费用不够的时候就下掉广告
                        $merchant = Func::getWho($ad->merchantId, UserType::TYPE_MERCHANT);
                        $unsettledAmount = MerchantService::getInstance()->getUnsettledAmount($merchant->merchantId);

                        if (bcsub($merchant->balance, $unsettledAmount, 4) >= $ad->cost) {
                            // 余额充足的情况下给广告商计费
                            $duplicate['totalCost'] = QueryBuilder::inc($ad->cost);

                            MerchantService::getInstance()->addUnsettledBill($merchant->merchantId, $date, $ad->cost);

                            $recordCost = true;
                        } else {
                            // 如果余额已经不够了，则将该广告下架
                            $ad->update(['status' => AdModel::STATE_FORBIDDEN]);
                            // 当然修改了之后要清除缓存
                            PageService::getInstance()->deleteTemplateCache([$page->pageTemplateId]);
                        }
                    }

                    // 2023-11-02 增加app留存点击数的判断
                    // 通过deviceId找app安装表中查看是不是留存用户，如果是则留存点击数加1，否则其他不变。
                    $channelInstall = ChannelInstallModel::create()->where(['deviceId' => $param['deviceId']])->get();
                    if ($channelInstall && $channelInstall['createDate'] < date('Y-m-d')) {
                        $retained = true;
                        $duplicate['retainedClickCount'] = QueryBuilder::inc();
                    } else {
                        $retained = false;
                    }
                    // 2023-11-15 增加标记是app点击还是h5点击，判断方法就是看设备id是不是在安装列表中
                    if ($channelInstall && in_array($channelInstall['source'], ChannelInstallModel::SOURCE_APP_LIST)) {
                        $appClick = true;
                        $duplicate['appClickCount'] = QueryBuilder::inc();
                    } else {
                        $appClick = false;
                        $duplicate['h5ClickCount'] = QueryBuilder::inc();
                    }

                    AdClickStatisticModel::create()
                        ->data([
                            'pageId' => $page->pageId,
                            'date' => $date,
                            'adId' => $ad->adId,
                            'clickCount' => 1,
                            'clickIpCount' => 1,
                            'retainedClickCount' => $retained ? 1 : 0,
                            'h5ClickCount' => $appClick ? 0 : 1,
                            'appClickCount' => $appClick ? 1 : 0,
                            'totalCost' => $recordCost ? $ad->cost : 0,
                        ])
                        ->duplicate($duplicate)
                        ->save();
                }

                // 2023-07-20 新增记录点击日志
                AdClickRecordModel::create()
                    ->data([
                        'pageId' => $page->pageId,
                        'date' => $date,
                        'deviceId' => $param['deviceId'],
                        'ipLong' => $ipLong,
                        'screen' => $param['screen']['width'] . 'x' . $param['screen']['height'],
                        'adId' => $ad->adId,
                        'ip' => $param['ip'],
                        'clickCount' => 1,
                        'firstTime' => $dateTime,
                        'latestTime' => $dateTime,
                    ])
                    ->duplicate([
                        'clickCount' => QueryBuilder::inc(),
                        'latestTime' => $dateTime,
                    ])
                    ->save();

                DbManager::getInstance()->commitWithCount();
            } catch (Throwable  $e) {
                //file_put_contents('take-error.log',date('Y-m-d H:i:s',time()).'-recordClick-'.json_encode($param).'-error:'.$e->getMessage()."\r\n",FILE_APPEND);
                DbManager::getInstance()->rollbackWithCount();
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return true;
        });
    }

    /**
     * 记录点击统计
     * @param $param
     */
    public function recordClickV2($param)
    {
        TaskManager::getInstance()->async(function () use ($param) {
            try {
                /**
                 * @var PageModel $page
                 */
                $time = time();
                $adId = &$param['adId'];
                $page = &$param['page'];
                $ipLong = ip2long($param['ip']);
                $date = date('Y-m-d', $time);
                $dateTime = date('Y-m-d H:i:s', $time);

                $duplicate = ['clickCount' => QueryBuilder::inc()];

                DbManager::getInstance()->startTransactionWithCount();

                $channelInstall = ChannelInstallModel::create()->where(['deviceId' => $param['deviceId']])->get();
                if ($channelInstall && $channelInstall['createDate'] < date('Y-m-d')) {
                    $retained = true;
                    $duplicate['retainedClickCount'] = QueryBuilder::inc();
                } else {
                    $retained = false;
                }
                // 2023-11-15 增加标记是app点击还是h5点击，判断方法就是看设备id是不是在安装列表中
                if ($channelInstall && in_array($channelInstall['source'], ChannelInstallModel::SOURCE_APP_LIST)) {
                    $appClick = true;
                    $duplicate['appClickCount'] = QueryBuilder::inc();
                } else {
                    $appClick = false;
                    $duplicate['h5ClickCount'] = QueryBuilder::inc();
                }

                AdClickStatisticModel::create()
                    ->data([
                        'pageId' => $page->pageId,
                        'date' => $date,
                        'adId' => $adId,
                        'clickCount' => 1,
                        'clickIpCount' => 1,
                        'retainedClickCount' => $retained ? 1 : 0,
                        'h5ClickCount' => $appClick ? 0 : 1,
                        'appClickCount' => $appClick ? 1 : 0,
                        'totalCost' => 0,
                    ])
                    ->duplicate($duplicate)
                    ->save();

                // 2023-07-20 新增记录点击日志
                AdClickRecordModel::create()
                    ->data([
                        'pageId' => $page->pageId,
                        'date' => $date,
                        'deviceId' => $param['deviceId'],
                        'ipLong' => $ipLong,
                        'screen' => $param['screen']['width'] . 'x' . $param['screen']['height'],
                        'adId' => $adId,
                        'ip' => $param['ip'],
                        'clickCount' => 1,
                        'firstTime' => $dateTime,
                        'latestTime' => $dateTime,
                    ])
                    ->duplicate([
                        'clickCount' => QueryBuilder::inc(),
                        'latestTime' => $dateTime,
                    ])
                    ->save();

                DbManager::getInstance()->commitWithCount();
            } catch (Throwable  $e) {
                DbManager::getInstance()->rollbackWithCount();
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return true;
        });
    }

    public function getStatisticTemporaryIpBit($date, $pageId, $adId, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        list($left, $right) = Func::splitIpLong($ipLong);
        $key = AdKey::clickStatTempIpLongBit($date, $pageId, $adId, $left);
        return $redis->getBit($key, $right);
    }

    public function setStatisticTemporaryIpBit($date, $pageId, $adId, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        list($left, $right) = Func::splitIpLong($ipLong);
        $key = AdKey::clickStatTempIpLongBit($date, $pageId, $adId, $left);
        $result = $redis->setBit($key, $right, $value);
        $time = Func::getExpireTime($pageId);
        $redis->expire($key, $time);
        return $result;
    }

    /**
     * ### 注意 ###
     * 这样写是不同设备同一个ip也会单独计算为一个，会让该ip的点击计数+1，如果想要不区分设备，就按照ip来区分，则需要在这里把get和set方法中的
     * 获取key的方法中的deviceId去掉才可以哈。
     * 意思就是 将 AdKey::clickStatTempIpLongHash 方法的去掉deviceId
     * @param $date
     * @param $deviceId
     * @param $pageId
     * @param $adId
     * @param $ipLong
     * @return false|string
     */
    public function getStatisticTemporaryIpHash($date, $deviceId, $pageId, $adId, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = AdKey::clickStatTempIpLongHash($date, $deviceId, $pageId, $adId);
        return $redis->hGet($key, $ipLong);
    }

    public function setStatisticTemporaryIpHash($date, $deviceId, $pageId, $adId, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = AdKey::clickStatTempIpLongHash($date, $deviceId, $pageId, $adId);
        $result = $redis->hIncrBy($key, $ipLong, $value);
        $time = Func::getExpireTime($pageId);
        $redis->expire($key, $time);
        return $result;
    }
}