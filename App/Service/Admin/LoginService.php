<?php

namespace App\Service\Admin;

use App\Enum\RedisDb;
use App\Enum\UserType;
use App\Model\Admin\AdminModel;
use App\RedisKey\SystemRedisKey;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\Hash;
use Exception;

class LoginService
{
    use Singleton;

    /**
     * @param $account
     * @param $password
     * @return AdminModel
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function login($account, $password): AdminModel
    {
        $admin = AdminModel::create()->get(['adminAccount' => $account, 'status' => [AdminModel::STATE_DELETED, '<>']]);

        if (!$admin) {
            throw new Exception('用户名或密码错误', Status::CODE_BAD_REQUEST);
        }

        if ($admin->status != AdminModel::STATE_NORMAL) {
            throw new Exception('账号状态异常请联系管理员', Status::CODE_BAD_REQUEST);
        }

        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $loginFailureLimitKey = SystemRedisKey::loginFailureLimit($account, UserType::TYPE_SYSTEM);
        // $key=Hash::makePasswordHash($password);
        // throw new Exception($key, Status::CODE_BAD_REQUEST);
        if (!Hash::validatePasswordHash($password, $admin->adminPassword)) {
            // 密码错误记录错误次数，5次后冻结用户
            $failureCount = $redis->get($loginFailureLimitKey);
            if (!$failureCount) {
                $redis->setEx($loginFailureLimitKey, 86400, 1);
            } else {
                $redis->incr($loginFailureLimitKey);
            }

            throw new Exception('用户名或密码错误', Status::CODE_BAD_REQUEST);
        }

        // 如果成功登录则清楚错误次数记录
        $redis->del($loginFailureLimitKey);

        return $admin;
    }
}