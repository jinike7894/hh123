<?php

namespace App\Service\Merchant;

use App\Enum\RedisDb;
use App\Model\Merchant\ChannelCpaConfigModel;
use App\Model\Merchant\ChannelDownloadModel;
use App\Model\Merchant\ChannelInstallModel;
use App\Model\Merchant\ChannelInstallStatisticModel;
use App\Model\Merchant\ChannelModel;
use App\Model\Navigation\PageModel;
use App\RedisKey\Navigation\PageKey;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

class ChannelService
{
    use Singleton;

    public function addChannel($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $existing = ChannelModel::create()->checkKeyExists($data['channelKey']);

            if ($existing) {
                throw new Exception('该渠道已存在', Status::CODE_BAD_REQUEST);
            }

            if ($data['channelDomain']) {
                $existing = ChannelModel::create()->checkDomainExists($data['channelDomain']);

                if ($existing) {
                    throw new Exception('该域名已绑定', Status::CODE_BAD_REQUEST);
                }
            }

            $channelId = ChannelModel::create($data)->save();

            if (!$channelId) {
                throw new Exception('渠道添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $channelId;
    }

    public function editChannel($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $channel = ChannelModel::create()->get($data['channelId']);

            if (!$channel) {
                throw new Exception('无效的渠道id', Status::CODE_BAD_REQUEST);
            }

            if (isset($data['channelKey']) && $data['channelKey']) {
                $existing = ChannelModel::create()
                    ->where(['channelId' => [$data['channelId'], '<>']])
                    ->checkKeyExists($data['channelKey']);

                if ($existing) {
                    throw new Exception('该渠道已存在', Status::CODE_BAD_REQUEST);
                }
            }

            if (isset($data['channelDomain']) && $data['channelDomain']) {
                $existing = ChannelModel::create()
                    ->where(['channelId' => [$data['channelId'], '<>']])
                    ->checkDomainExists($data['channelDomain']);

                if ($existing) {
                    throw new Exception('该域名已绑定', Status::CODE_BAD_REQUEST);
                }
            }

            $result = $channel->update($data);

            if (!$result) {
                throw new Exception('渠道修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            if(isset($data['cpaCost']) && isset($data['coefficient'])){
                $cpaConfigExisting = ChannelCpaConfigModel::create()
                    ->where(['date' => date('Y-m-d')])
                    ->where(['channelId' => $data['channelId']])
                    ->get();
                if(!$cpaConfigExisting){
                    $cpaConfigData = [
                        'date' => date('Y-m-d'),
                        'channelId' => $data['channelId'],
                        'cpaCost' => $data['cpaCost'],
                        'coefficient' => $data['coefficient'],
                    ];
                    ChannelCpaConfigModel::create($cpaConfigData)->save();
                }else{
                    ChannelCpaConfigModel::create()
                        ->where([
                            'date' => date('Y-m-d'),
                            'channelId' => $data['channelId'],
                        ])
                        ->update([
                            'cpaCost' => $data['cpaCost'],
                            'coefficient' => $data['coefficient'],
                        ]);
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
     * 记录安装统计数据
     * 1.2023-09-26更新，将安卓ios等渠道拆出来单独统计一个字段，然后所有的安装都按照设备id来判断，不再将h5书签通过ip判断
     * 2.因为增加了渠道域名，所以渠道key也变为了可选参数，需要做两者的验证。
     * @param $data
     * @return bool
     * @throws Throwable
     */
    public function recordInstall($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            // 这里已经换成了2个都可以获取了
            if ($data['channelKey']) {
                $channel = ChannelModel::create()
                    ->where([
                        'channelKey' => $data['channelKey'],
                        'status' => [ChannelModel::STATE_DELETED, '>'],
                    ])
                    ->get();


                if (!$channel) {
                    // 2023-12-19 改为自动创建渠道
                    list(, $channel) = AutoChannelService::getInstance()->autoCreatePageAndChannel($data['channelKey']);

                    // throw new Exception('无效的渠道key', Status::CODE_BAD_REQUEST);
                }
            } else {
                $channel = ChannelModel::create()
                    ->where([
                        'channelDomain' => $data['channelDomain'],
                        'status' => [ChannelModel::STATE_DELETED, '>'],
                    ])
                    ->get();

                if (!$channel) {
                    throw new Exception('无效的渠道域名', Status::CODE_BAD_REQUEST);
                }
            }

            // 解释一下现在的逻辑
            // 之前是只有原生app才用设备id，后面h5采用了 https://github.com/fingerprintjs/fingerprintjs 的库后
            // 现在也采用设备id的方式来判断，就不用唯一一次的ip了。
            $channelInstall = ChannelInstallModel::create()
                ->where([
                    'deviceId' => $data['deviceId'],
                ])
                ->get();

            $now = time();
            $nowDate = date('Y-m-d', $now);

            if ($channelInstall) {
                // 如过存在数据，则只是判断是否已记录今日活跃
                // 当只有最后活跃日期小于今天，则代表是没有记录过的，则需要记录活跃。
                if ($channelInstall->latestActiveDate < $nowDate) {
                    $channelInstall->update(['latestActiveDate' => $nowDate]);

                    $this->countActive($nowDate, $channelInstall->channelId, $data['source'], $channelInstall->isCounted);
                }

            } else {
                // 不存在数据才是需要记录的新安装
                // 注意这里只需要补充没有的字段就好，补分字段已经传进来了。
                $data['channelId'] = $channel->channelId;
                $data['isCounted'] = mt_rand(1, 100) <= $channel->percentage ? 1 : 0; // 2.要根据配置的渠道百分比来计算是否统计这条数据
                $data['latestActiveDate'] = $nowDate;
                $data['createDate'] = $nowDate;
                $pastSeconds = Func::getPastSeconds($now);
                $data['createTimeBucketHour'] = floor($pastSeconds / 3600);
                $data['createTimeBucketHalfHour'] = floor($pastSeconds / 1800);

                $result = ChannelInstallModel::create($data)->save();
                if (!$result) {
                    throw new Exception('记录渠道安装信息失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }

                $this->countInstall($nowDate, $channel->channelId, $data['source'], $data['isCounted']);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }


    /**
     * 记录点击下载统计数据
     * @param $data
     * @return bool
     * @throws Throwable
     */
    public function recordDownBtn($data)
    {
        TaskManager::getInstance()->async(function () use ($data) {
            try {
                $channel = ChannelModel::create()
                    ->where([
                        'channelKey' => $data['channelKey'],
                    ])
                    ->get();

                if (!$channel) {
                    // 2023-12-19 改为自动创建渠道
                    list(, $channel) = AutoChannelService::getInstance()->autoCreatePageAndChannel($data['channelKey']);

                    // throw new Exception('无效的渠道key', Status::CODE_BAD_REQUEST);
                }

                $now = time();
                $nowDate = date('Y-m-d', $now);
                $ipLong = ip2long($data['ip']);
                $isCounted = $this->getDownloadStatisticTemporaryIpHash($nowDate, $data['channelKey'], $ipLong);

                if(!$isCounted){
                    $this->setDownloadStatisticTemporaryIpHash($nowDate, $data['channelKey'], $ipLong, 1);
                    $saveData['channelId'] = $channel->channelId;
                    $saveData['date'] = $nowDate;
                    $saveData['downClick'] = 1;
                    $duplicate['downClick'] = QueryBuilder::inc();
                    $result = ChannelDownloadModel::create()
                        ->data($saveData)
                        ->duplicate($duplicate)
                        ->save();
                    if (!$result) {
                        throw new Exception('记录点击下载按钮次数失败', Status::CODE_INTERNAL_SERVER_ERROR);
                    }
                }
            }catch (Throwable  $e) {
        throw new Exception($e->getMessage(), $e->getCode());
            }
        });
        return true;
    }

    /**
     * 仅添加活跃计数
     * @param string $date 日期
     * @param int $channelId 渠道id
     * @param string $source 来源名称
     * @param bool $isCounted 该安装记录是否为要统计的记录（非扣量数据）
     * @return bool
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function countActive($date, $channelId, $source, $isCounted)
    {
        $activeKey = 'active' . $source;
        $realActiveKey = 'realActive' . $source;

        $duplicate = [
            $realActiveKey => QueryBuilder::inc(),
            'realActiveTotal' => QueryBuilder::inc(),
        ];

        if ($isCounted) {
            $duplicate[$activeKey] = QueryBuilder::inc();
            $duplicate['activeTotal'] = QueryBuilder::inc();
        }

        // 因为统计活跃数据可能比安装数据先出现，所以任然要以不存在添加，存在修改的方式来操作。
        $result = ChannelInstallStatisticModel::create()
            ->data([
                'date' => $date,
                'channelId' => $channelId,
                'count' => 0,
                'realCount' => 0,
                $activeKey => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                $realActiveKey => 1,
                'activeTotal' => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                'realActiveTotal' => 1,
            ])
            ->duplicate($duplicate)
            ->save();

        if (!$result) {
            throw new Exception('记录渠道安装统计信息失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        return true;
    }

    /**
     * 添加安装计数同时也要加上活跃计数
     * @param string $date 日期
     * @param int $channelId 渠道id
     * @param string $source 来源名称
     * @param bool $isCounted 该安装记录是否为要统计的记录（非扣量数据）
     * @return bool
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function countInstall($date, $channelId, $source, $isCounted)
    {
        $installKey = 'install' . $source;
        $realInstallKey = 'realInstall' . $source;
        $activeKey = 'active' . $source;
        $realActiveKey = 'realActive' . $source;

        // 真实数据都要加1所有直接就写上
        $duplicate = [
            $realInstallKey => QueryBuilder::inc(),
            $realActiveKey => QueryBuilder::inc(),
            'realInstallTotal' => QueryBuilder::inc(),
            'realActiveTotal' => QueryBuilder::inc(),
        ];

        // 扣量后的统计是否要加1就先判断
        if ($isCounted) {
            $duplicate[$installKey] = QueryBuilder::inc();
            $duplicate[$activeKey] = QueryBuilder::inc();
            $duplicate['installTotal'] = QueryBuilder::inc();
            $duplicate['activeTotal'] = QueryBuilder::inc();
        }

        $result = ChannelInstallStatisticModel::create()
            ->data([
                'date' => $date,
                'channelId' => $channelId,
                $installKey => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                $realInstallKey => 1,
                $activeKey => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                $realActiveKey => 1,
                'installTotal' => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                'realInstallTotal' => 1,
                'activeTotal' => $isCounted ? 1 : 0, // 如果是不计数的数据则只增加真实数据
                'realActiveTotal' => 1,
            ])
            ->duplicate($duplicate)
            ->save();

        if (!$result) {
            throw new Exception('记录渠道安装统计信息失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        return true;
    }

    /**
     * 通过页面名或渠道key获取对应的页面id和渠道id
     * @param $pageName
     * @return array
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function getPageAndChannelIdByPageName($pageName)
    {
        $pageId = PageModel::create()->where(['pageName' => $pageName])->val('pageId');
        $channelId = ChannelModel::create()->where(['channelKey' => $pageName])->val('channelId');

        $pageId || $pageId = 0;
        $channelId || $channelId = 0;

        return ['pageId' => $pageId, 'channelId' => $channelId];
    }

    public function getDownloadStatisticTemporaryIpHash($date, $channelKey, $ipLong)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticDownloadClickTempIpLongHash($date, $channelKey);
        return $redis->hGet($key, $ipLong);
    }

    public function setDownloadStatisticTemporaryIpHash($date, $channelKey, $ipLong, $value)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        $key = PageKey::statisticDownloadClickTempIpLongHash($date, $channelKey);
        $result = $redis->hSet($key, $ipLong, $value);
        $time = Func::getRemainingSeconds();
        $redis->expire($key, $time);
        return $result;
    }
}