<?php

namespace App\Crontab\Merchant;

use App\Service\Merchant\MerchantService;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 商户账单结算
 * 手动执行 php easyswoole crontab run --name=MerchantBillSettlement
 * Class MerchantCommissionCrontab
 * @package App\Crontab\Merchant
 */
class MerchantBillSettlementCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'MerchantBillSettlement';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '1 0 * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始进行商户账单结算', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                // 只从昨天的开始结算，当日的不参与结算。如果结算，那么计费逻辑会出错的，哈哈哈。
                $date = date('Y-m-d', strtotime('-1 day'));
                $unsettledBillList = MerchantService::getInstance()->getUnsettledBillList($date);

                if (!$unsettledBillList) {
                    LogHandler::getInstance()->logCustomFile('目前没有需要结算的账单 参数：$date:' . $date, $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                    RedisLock::releaseLock($lockKey, $lockValue);
                    return true;
                }

                DbManager::getInstance()->startTransactionWithCount();

                foreach ($unsettledBillList as $bill) {
                    LogHandler::getInstance()->logCustomFile('开始结算merchantId:' . $bill['merchantId'] . ' amount:' . $bill['amount'], $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                    $change = MerchantService::getInstance()->settleBill($bill);
                    LogHandler::getInstance()->logCustomFile('结算完成merchantId:' . $change['merchantId'] . ' amount:' . $change['amount'], $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                }

                DbManager::getInstance()->commitWithCount();

                LogHandler::getInstance()->logCustomFile('商户账单结算完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
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