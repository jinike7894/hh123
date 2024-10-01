<?php

namespace App\Crontab\Navigation;

use App\Model\Merchant\ChannelCostStatisticModel;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 更新渠道成本统计
 * 手动执行 php easyswoole crontab run --name=ChannelCostStatistics
 * Class ChannelCostStatisticsCrontab
 * @package App\Crontab\Navigation
 */
class ChannelCostStatisticsCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'ChannelCostStatistics';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '*/10 * * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始统计渠道成本记录', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                $channelCosts = ChannelCostStatisticModel::create()->all();
                foreach ($channelCosts as $channelCost){
                    $response = file_get_contents($channelCost['apiUrl']);
                    if($response){
                        ChannelCostStatisticModel::create()->where(['channelCostId' => $channelCost['channelCostId']])->update(['dhJson' => $response]);
                    }
                }
                RedisLock::releaseLock($lockKey, $lockValue);
            } catch (Throwable  $e) {
                RedisLock::releaseLock($lockKey, $lockValue);
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return true;
        });
    }

    public function onException(Throwable $throwable)
    {
        LogHandler::getInstance()->logCustomFile($throwable->getMessage(), $this->logName(), LogHandler::LOG_LEVEL_ERROR, 'error');
    }
}