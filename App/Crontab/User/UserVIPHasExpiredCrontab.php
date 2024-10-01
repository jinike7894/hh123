<?php

namespace App\Crontab\User;

use App\Model\User\UserGroupModel;
use App\Model\User\UserModel;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 用户VIP已过期
 * 更新过期用户的用户组。
 * 手动执行 php easyswoole crontab run --name=UserVIPHasExpired
 * Class UserVIPHasExpiredCrontab
 * @package App\Crontab\User
 */
class UserVIPHasExpiredCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'UserVIPHasExpired';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '3 0 * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始处理VIP过期的用户', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                $overtimeUserCount = UserModel::create()
                    ->where([
                        'userGroupId' => UserGroupModel::GROUP_VIP_ID,
                        'userGroupExpiryDate' => [date('Y-m-d'), '<']
                    ])
                    ->count();

                if (!$overtimeUserCount) {
                    LogHandler::getInstance()->logCustomFile('目前没有需要处理的VIP过期用户', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                    RedisLock::releaseLock($lockKey, $lockValue);
                    return true;
                }

                DbManager::getInstance()->startTransactionWithCount();

                $result = UserModel::create()
                    ->where([
                        'userGroupId' => UserGroupModel::GROUP_VIP_ID,
                        'userGroupExpiryDate' => [date('Y-m-d'), '<']
                    ])
                    ->update([
                        'userGroupId' => UserGroupModel::GROUP_ORDINARY_ID,
                    ]);

                if ($result === false) {
                    throw new Exception('用户会员数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }

                DbManager::getInstance()->commitWithCount();

                LogHandler::getInstance()->logCustomFile('处理VIP过期的用户完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
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