<?php

namespace App\HttpController\Api\User;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\Enum\UserType;
use App\HttpController\Api\ApiBase;
use App\Model\Common\ConfigModel;
use App\Model\User\UserModel;
use App\RedisKey\SystemRedisKey;
use App\Service\Common\OnlineService;
use App\Service\User\UserService;
use App\Utility\Func;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Http\Message\Status;
use EasySwoole\Jwt\Jwt;
use EasySwoole\RedisPool\RedisPool;

class UserBase extends ApiBase
{
    // public 才会根据协程清除
    public $jwt;
    /**
     * @var array
     */
    public $who = [];

    // 白名单
    protected $basicAction = [
        // 登录的
        '/Api/User/Login/deviceLogin', // 设备登录
        '/Api/User/Login/identityCardLogin', // 通过身份卡登陆
        '/Api/User/Login/messageLogin', // 通过短信登陆

        // 不需要登录就能获取数据的
        '/Api/Video/Adult/getTypeList', // 获取成人分类列表
        '/Api/Video/Adult/adultList', // 获取成人视频列表
        '/Api/Prostitute/ProstituteType/prostituteTypeList', // 获取楼凤分类列表
        '/Api/Prostitute/Prostitute/prostituteList', // 获取楼凤列表
        '/Api/Video/ShortVideo/videoList', // 获取短视频列表
        '/Api/Live/Live/liveList', // 获取直播列表
        '/Api/Market/Vip/giftUserVipDays'//给性浪游戏赠送vip天数的
    ];

    /**
     * 获取用户token数据（因多个地方用到所以统一）
     * @param UserModel $user
     * @return array
     */
    function getUserTokenData(UserModel $user)
    {
        return [
            'userId' => $user->userId,
            // 因为用户组会变动，记录在这里就有点不好，如果不刷新token就不会刷新用户组。
            // 所以我反正感觉去查一次拿当前用户组是可以的，如果要放在这就得重新登录刷新token就好。
            //'userGroupId' => $user->userGroupId,
            'userType' => UserType::TYPE_MEMBER,
            'nickname' => $user->nickname,
            'deviceId' => $user->deviceId,
        ];
    }

    /**
     * 获取用户登录时返回的数据（因多个地方用到所以统一）
     * @param UserModel $user
     * @return array
     */
    function getUserLoginData(UserModel $user)
    {
        return [
            'userId' => $user->userId,
            'userGroupId' => $user->userGroupId,
            'userGroupExpiryDate' => $user->userGroupExpiryDate, // 会员组有效期
            'AiFaceImg' => $user->AiFaceImg,
            'AiFaceVideo' => $user->AiFaceVideo,
            'AiPicture' => $user->AiPicture,
            'nickname' => $user->nickname,
            'balance' => $user->balance,
            'phoneState' => $user->phoneNumber ? 1 : 0, // 是否绑定手机号
            'identityCard' => UserService::getInstance()->getIdentityCard($user),
        ];
    }

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws \Throwable
     */
    function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }

        // 判断是否开启维护
        $maintain = ConfigModel::create()->getConfigValue(SystemConfigKey::WEBSITE_MAINTENANCE);
        $maintain = json_decode($maintain, true);
        if ($maintain['status'] == 1) {
            throw new \Exception($maintain['content'], Status::CODE_BAD_REQUEST);
        }

        $path = $this->request()->getUri()->getPath();

        // 不需要登录的则跳过
        if (!Func::inArrayNoCase($path, $this->basicAction)) {
            // 获取JWT信息
            $status = $this->checkJwt();
            if ($status < 0) {
                switch ($status) {
                    case  -1:
                        // echo '无效';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入Token无效');
                        break;
                    case  -2:
                        // echo 'token过期';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入已过期');
                        break;
                    case  -3:
                        // echo 'token解析异常';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入检查失败');
                        break;
                }
                return false;
            }

            // 这里刷新在线状态
            OnlineService::getInstance()->record($this->who['userId'], $this->who['userType']);
        }

        return true;
    }

    function checkJwt(): int
    {
        $token = $this->request()->getHeader('authorization');
        if (empty($token)) {
            return -1;
        }

        try {
            $token = current($token);
            $jwtConfig = Config::getInstance()->getConf('JWT');
            $jwtObject = Jwt::getInstance()->setSecretKey($jwtConfig['secretKey'])->decode($token);

            $status = $jwtObject->getStatus();
            if ($status == 1) {

                if ($jwtConfig['issuer'] != $jwtObject->getIss()) {
                    return -1;
                }

                $this->jwt = $jwtObject;
                $this->who = $jwtObject->getData();
            }

            // 这里有个特别特殊的处理
            // 如果开启了只允许单设备登录则要将jwt和对应的用户id存入redis中当做session来使用
            // 所以这里要进行token的对比验证
            if ($status != -2) {
                $forceSingleDeviceLogin = ConfigModel::create()->getConfigValue(SystemConfigKey::FORCE_SINGLE_DEVICE_LOGIN);
                if ($forceSingleDeviceLogin) {
                    $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
                    $key = SystemRedisKey::session($this->who['userId'], $this->who['userType']);
                    $cacheToken = $redis->get($key);
                    if ($token != $cacheToken) {
                        return -2;
                    }
                }
            }

        } catch (\EasySwoole\Jwt\Exception $e) {
            return -3;
        }

        return $status;
    }
}