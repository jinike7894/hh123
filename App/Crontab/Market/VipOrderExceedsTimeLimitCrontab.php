<?php

namespace App\Crontab\Market;

use App\Model\User\UserVipOrderModel;
use App\Service\Market\VipOrderService;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * VIP订单超时自动取消
 * 这个任务只是作为保底检查有没有漏网没有处理到的订单
 * 手动执行 php easyswoole crontab run --name=VipOrderExceedsTimeLimit
 * Class VipOrderExceedsTimeLimitCrontab
 * @package App\Crontab\Market
 */
class VipOrderExceedsTimeLimitCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'VipOrderExceedsTimeLimit';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '0 * * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始处理超时的VIP订单', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                $overtimeOrderList = UserVipOrderModel::create()
                    ->where([
                        'status' => UserVipOrderModel::STATE_WAITING_BUYERS_PAYMENT,
                        'createTime' => [date('Y-m-d H:i:s', strtotime('-3 days')), '<']
                    ])
                    ->all();

                if (!$overtimeOrderList) {
                    LogHandler::getInstance()->logCustomFile('目前没有需要处理的超时VIP订单', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                    RedisLock::releaseLock($lockKey, $lockValue);
                    return true;
                }

                DbManager::getInstance()->startTransactionWithCount();

                foreach ($overtimeOrderList as $item) {
                    VipOrderService::getInstance()->buyerPaymentTimeout($item->orderNo);
                }

                DbManager::getInstance()->commitWithCount();

                LogHandler::getInstance()->logCustomFile('处理超时的VIP订单完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                RedisLock::releaseLock($lockKey, $lockValue);
            } catch (Throwable  $e) {
                DbManager::getInstance()->rollbackWithCount();
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