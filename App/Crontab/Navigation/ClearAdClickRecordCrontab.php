<?php

namespace App\Crontab\Navigation;

use App\Model\Navigation\AdClickRecordModel;
use App\Model\Navigation\AdClickStatisticModel;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 清除广告点击记录
 * 手动执行 php easyswoole crontab run --name=ClearAdClickRecord
 * Class ClearAdClickRecordCrontab
 * @package App\Crontab\Navigation
 */
class ClearAdClickRecordCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'ClearAdClickRecord';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '0 */10 * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始清除广告点击记录', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                $date = date('Y-m-d', strtotime('-3 day'));

                AdClickRecordModel::create()
                    ->where([
                        'date' => [$date, '<'],
                    ])
                    ->destroy();



                $date = date('Y-m-d', strtotime('-10 day'));

                AdClickStatisticModel::create()
                    ->where([
                        'date' => [$date, '<'],
                    ])
                    ->destroy();

                LogHandler::getInstance()->logCustomFile('清除广告点击记录完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
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