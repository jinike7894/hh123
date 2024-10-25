<?php

namespace App\Service\User;

use App\Enum\RedisDb;
use App\Model\User\UserGroupModel;
use App\Model\User\UserInviteModel;
use App\Model\User\UserModel;
use App\RedisKey\User\UserKey;
use App\Service\Merchant\ChannelService;
use App\Service\Message\JSMSService;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use App\Model\User\UserModel;
use Exception;
use Throwable;

class UserService
{
    use Singleton;

    /**
     * 通过设备id登录（含自动注册账号）
     * @param array $data
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function deviceLogin($data): UserModel
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $deviceId = $data['deviceId'] ?? '';
            $ip = $data['ip'] ?? '';
            $pageName = $data['pageName'] ?? '';
            $inviteCode = $data['inviteCode'] ?? '';
            $ipLong = ip2long($ip);

            // 1.通过设备id查询是否已经注册过账号，有则直接返回
            // 特别注意，这里并没有限制必须是status=1 因为目前deviceId是唯一的，如果有调整再说
            $user = UserModel::create()
                ->where([
                    'deviceId' => $deviceId,
                    'status' => [UserModel::STATE_DELETED, '<>'],
                ])
                ->get();

            if ($user) {
                if ($user->status != UserModel::STATE_NORMAL) {
                    throw new Exception('账号状态异常', Status::CODE_BAD_REQUEST);
                }

                $user->updateLastLoginInfo($ipLong, date('Y-m-d H:i:s'));
            } else {
                // 2.没有注册过则检查当前ip是否可以自动注册
                $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
                $key = UserKey::deviceCreateAccount($ipLong, date('Y-m-d'));
                $times = $redis->incr($key);
                if ($times >= 100) {
                    throw new Exception('ip超过最大注册限制', Status::CODE_BAD_REQUEST);
                }
                if ($times == 1) {
                    $redis->expire($key, Func::getRemainingSeconds());
                }
                // 2023-11-25 注册时增加关联的渠道id
                $channel = ChannelService::getInstance()->getPageAndChannelIdByPageName($pageName);
                // 3.成功自动注册后返回账号
                $user = UserModel::create([
                    'userGroupId' => UserGroupModel::GROUP_TOURIST_ID,
                    'pageId' => $channel['pageId'],
                    'channelId' => $channel['channelId'],
                    'deviceId' => $deviceId,
                    'nickname' => '游客' . strtoupper(uniqid()),
                    'regIpLong' => $ipLong,
                    'regDate' => date('Y-m-d'),
                    'status' => UserModel::STATE_NORMAL,
                    'balance' => '0.00',
                    'lastLoginTime' => date('Y-m-d H:i:s'),
                    'lastLoginIpLong' => $ipLong,
                ]);

                $userId = $user->save();

                if ($userId === false) {
                    throw new Exception('创建用户失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
                // 新创建用户检查是否给邀请人奖励
                if ($inviteCode) {
                    $inviter = UserModel::create()->lockForUpdate()->get($inviteCode);
                    if ($inviter) {
                        $this->rewardInviter($inviter, $user);
                    }
                }
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $user;
    }

    /**
     * 身份卡登录
     * @param $data
     * @return UserModel
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function identityCardLogin($data): UserModel
    {
        $identityCard = $data['identityCard'] ?? '';
        $ip = $data['ip'] ?? '';
        $ipLong = ip2long($ip);

        $user = $this->getUserByIdentityCard($identityCard);

        if ($user->status != UserModel::STATE_NORMAL) {
            throw new Exception('账号状态异常', Status::CODE_BAD_REQUEST);
        }

        $user->updateLastLoginInfo($ipLong, date('Y-m-d H:i:s'));
        return $user;
    }

    /**
     * 短信登录
     * @param $data
     * @return UserModel
     * @throws Throwable
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function messageLogin($data): UserModel
    {
        $phoneNumber = $data['phoneNumber'] ?? '';
        $messageId = $data['messageId'] ?? '';
        $code = $data['code'] ?? '';
        $ip = $data['ip'] ?? '';
        $ipLong = ip2long($ip);

        $checkResult = JSMSService::getInstance()->checkCode($phoneNumber, $messageId, $code);

        if (!$checkResult) {
            throw new Exception('验证码不正确，请稍后重试。', Status::CODE_BAD_REQUEST);
        }

        $user = UserModel::create()->get(['phoneNumber' => $phoneNumber]);

        if (!$user) {
            throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
        }

        if ($user->status != UserModel::STATE_NORMAL) {
            throw new Exception('账号状态异常', Status::CODE_BAD_REQUEST);
        }

        $user->updateLastLoginInfo($ipLong, date('Y-m-d H:i:s'));
        return $user;
    }

    /**
     * 获取身份证
     * @param $user
     * @return false|string
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getIdentityCard($user)
    {
        // 这是为了确认有这个用户
        if (!($user instanceof UserModel) && is_numeric($user)) {
            $user = UserModel::create()->get($user);
        }

        if (!($user instanceof UserModel)) {
            throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
        }

        $jwtConfig = Config::getInstance()->getConf('JWT');
        return Func::encrypt(strval($user->userId), $jwtConfig['secretKey']);
    }

    /**
     * 通过身份卡获取用户
     * @param $identityCard
     * @return UserModel
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getUserByIdentityCard($identityCard)
    {
        $jwtConfig = Config::getInstance()->getConf('JWT');
        $plaintext = Func::decrypt($identityCard, $jwtConfig['secretKey']);

        if (!$plaintext) {
            throw new Exception('用户身份卡信息错误', Status::CODE_BAD_REQUEST);
        }

        $user = UserModel::create()->get($plaintext);

        if (!$user) {
            throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
        }

        return $user;
    }

    /**
     * 绑定手机
     * @param $user
     * @param $phoneNumber
     * @return bool
     * @throws Throwable
     */
    public function bindCellPhone($user, $phoneNumber, $phoneCountryCode)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            // 这是为了确认有这个用户
            if (!($user instanceof UserModel) && is_numeric($user)) {
                $user = UserModel::create()->lockForUpdate()->get($user);
            }

            if (!($user instanceof UserModel)) {
                throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
            }

            # TODO: 这里业务并没有说明要不要判断该账号已经绑定过手机号，目前是直接覆盖新的。

            $exists = UserModel::create()->get([
                'phoneCountryCode' => $phoneCountryCode,
                'phoneNumber' => $phoneNumber,
            ]);
            if ($exists) {
                throw new Exception('该手机号已经绑定过了', Status::CODE_BAD_REQUEST);
            }

            $update = [
                'phoneCountryCode' => $phoneCountryCode,
                'phoneNumber' => $phoneNumber,
            ];

            if ($user->userGroupId == UserGroupModel::GROUP_TOURIST_ID) {
                $update['userGroupId'] = UserGroupModel::GROUP_ORDINARY_ID; // 绑定手机之后就是正式会员了，需要把用户组给改掉。
            }

            $result = $user->update($update);

            if ($result === false) {
                throw new Exception('手机号绑定失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 增加用户的VIP天数
     * @param $user
     * @param $days
     * @param string $nowDate
     * @return bool
     * @throws Throwable
     */
    public function increaseVIPDays($user, $days, $nowDate = '')
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            // 这是为了确认有这个用户
            if (!($user instanceof UserModel) && is_numeric($user)) {
                $user = UserModel::create()->lockForUpdate()->get($user);
            }

            if (!($user instanceof UserModel)) {
                throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
            }

            $nowDate || $nowDate = date('Y-m-d');

            // 解释一下，如果当前日期已经过了有效期，则用当前日期+购买的日期
            if ($user->userGroupExpiryDate < $nowDate) {
                $newExpiryDate = date('Y-m-d', strtotime("+{$days} day"));
            } else {
                // 如果当前日期没有超过有效期，则用有效期最后一天+购买的日期
                $newExpiryDate = date('Y-m-d', strtotime("+{$days} day", strtotime($user->userGroupExpiryDate)));
            }

            $result = $user->update(['userGroupExpiryDate' => $newExpiryDate, 'userGroupId' => UserGroupModel::GROUP_VIP_ID]);
            if ($result === false) {
                throw new Exception('用户会员数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 增加用户的ai天数
     * @param $user
     * @param $times
     * @param string $nowDate
     * @return bool
     * @throws Throwable
     */
    public function increaseAiTimes($user, $times, $aiType)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();
            // 这是为了确认有这个用户
            if (!($user instanceof UserModel) && is_numeric($user)) {
                $user = UserModel::create()->lockForUpdate()->get($user);
            }

            if (!($user instanceof UserModel)) {
                throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
            }

            $result = $user->update([$aiType => $user->$aiType + $times]);
            if ($result === false) {
                throw new Exception('用户会员数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    public function checkAiTimes($user, $aiType)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();
            // 这是为了确认有这个用户
            if (!($user instanceof UserModel) && is_numeric($user)) {
                $user = UserModel::create()->lockForUpdate()->get($user);
            }

            if (!($user instanceof UserModel)) {
                throw new Exception('用户参数异常', Status::CODE_BAD_REQUEST);
            }
            if($user->$aiType <= 0){
               return true;
            }
            $result = $user->update([$aiType => $user->$aiType - 1]);
            if ($result === false) {
                throw new Exception('用户会员数据修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    public function rewardInviter(UserModel $inviter, UserModel $invitee)
    {
        try {
            // 邀请人和被邀请人不能是同一人
            if ($inviter->userId == $invitee->userId) {
                throw new Exception('邀请人参数异常', Status::CODE_BAD_REQUEST);
            }

            DbManager::getInstance()->startTransactionWithCount();

            $userInviteId = UserInviteModel::create([
                'inviterId' => $inviter->userId,
                'inviteeId' => $invitee->userId,
                'createDate' => date('Y-m-d'),
            ])->save();

            if ($userInviteId === false) {
                throw new Exception('用户邀请记录失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
            $userData=UserModel::create()->where(["userId"=>$inviter->userId])->get();
            // 现在给邀请者发奖励在这里
            // 现在的业务是邀请一个人，邀请者获得3天VIP
            if(time()<(strtotime($userData["createTime"])+86400*2)){
                $this->increaseVIPDays($inviter, 7);
            }else{
                $this->increaseVIPDays($inviter, 3);
            }
            

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}