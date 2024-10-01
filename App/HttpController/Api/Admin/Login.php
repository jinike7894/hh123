<?php

namespace App\HttpController\Api\Admin;

use App\Enum\ConfigKey\NavigationConfigKey;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\Enum\UserType;
use App\Enum\VerifyCodeType;
use App\Model\Admin\AdminLogsModel;
use App\Model\Admin\AdminModel;
use App\Model\Common\ConfigModel;
use App\RedisKey\SystemRedisKey;
use App\Service\Admin\LoginService;
use App\Utility\Func;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\RedisPool\RedisPool;
use PHPGangsta_GoogleAuthenticator;
use Exception;
use Throwable;

/**
 * Class Common
 * @package App\HttpController\Api\Admin
 * @ApiGroup(groupName="后台-登录 Admin/Login")
 * @ApiGroupDescription("后台登录相关的操作")
 */
class Login extends AdminBase
{
    /**
     * 管理后台登录
     * @Api(name="管理后台登录", path="/Api/Admin/Login/login")
     * @ApiDescription("管理后台登录（商户登录同接口，会有商户的信息）")
     * @Method(allow=["POST"])
     * @ApiRequestExample("curl http://127.0.0.1:9501/Api/Admin/Common/login")
     * @Param(name="adminAccount", alias="用户名", type="string", required="", lengthMax="20", description="用户登录名")
     * @Param(name="adminPassword", alias="密码", type="string", required="", lengthMin="6", lengthMax="20", description="用户密码")
     * @Param(name="verifyUniqueId", alias="验证唯一值", type="string", optional="", description="验证唯一值")
     * @Param(name="imgCode", alias="图形验证码", type="string", optional="", description="图形验证码")
     * @Param(name="safeCode", alias="google验证码", type="string", required="", mbLengthMin="0", description="google验证码")
     * @apiSuccess({"code":200,"result":{"token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2ODgwMzkwNjgsInN1YiI6bnVsbCwibmJmIjoxNjg3OTUyNjY4LCJhdWQiOiJ1c2VyIiwiaWF0IjoxNjg3OTUyNjY4LCJqdGkiOiJ2Znk1b3dkYjdTIiwiaXNzIjoiZXNkaCIsInN0YXR1cyI6MSwiZGF0YSI6eyJhZG1pbklkIjoyLCJhZG1pblR5cGUiOiJTeXN0ZW0iLCJhZG1pbkFjY291bnQiOiJ0b21teSIsImFkbWluTmlja25hbWUiOiJ0b21teSJ9fQ.UuuqMHteFHTo5f1o83AypQG1l449iIuQO1tKQf-2ICc","userInfo":{"adminId":2,"adminAccount":"tommy","adminNickname":"tommy","avatar":"","adminType":"System","adminEmail":"","adminMobile":"","isGoogleAuthenticator":0},"navigation":{"MerchantBalanceReminder":"1","ReminderAmount":"200","ReminderFrequency":"60"},"authList":[{"authId":1,"parentAuthId":0,"authName":"站点管理","authRule":"","authIcon":"","authType":0},{"authId":2,"parentAuthId":1,"authName":"站点配置列表","authRule":"/Api/Admin/System/Website/configList","authIcon":"","authType":0},{"authId":3,"parentAuthId":1,"authName":"站点配置设置","authRule":"/Api/Admin/System/Website/setConfig","authIcon":"","authType":1},{"authId":4,"parentAuthId":0,"authName":"权限管理","authRule":"","authIcon":"","authType":0},{"authId":5,"parentAuthId":4,"authName":"角色列表","authRule":"/Api/Admin/AuthsManage/Roles","authIcon":"","authType":0},{"authId":6,"parentAuthId":5,"authName":"角色添加","authRule":"/Api/Admin/AuthsManage/Roles/add","authIcon":"","authType":1},{"authId":7,"parentAuthId":5,"authName":"角色编辑","authRule":"/Api/Admin/AuthsManage/Roles/update","authIcon":"","authType":1},{"authId":8,"parentAuthId":4,"authName":"管理员列表","authRule":"/Api/Admin/Account/adminList","authIcon":"","authType":0},{"authId":9,"parentAuthId":8,"authName":"管理员添加","authRule":"/Api/Admin/Account/add","authIcon":"","authType":1},{"authId":10,"parentAuthId":8,"authName":"编辑管理员","authRule":"/Api/Admin/Account/update","authIcon":"","authType":1},{"authId":11,"parentAuthId":8,"authName":"管理员详情","authRule":"/Api/Admin/Account/getOne","authIcon":"","authType":1},{"authId":12,"parentAuthId":8,"authName":"管理员日志","authRule":"/Api/Admin/AuthsManage/AdminLogs","authIcon":"","authType":0},{"authId":13,"parentAuthId":8,"authName":"绑定google验证器","authRule":"/Api/Admin/Account/bindGoogleAuthenticator","authIcon":"","authType":1},{"authId":14,"parentAuthId":8,"authName":"验证绑定google验证器","authRule":"/Api/Admin/WhiteIp/add","authIcon":"","authType":1},{"authId":15,"parentAuthId":8,"authName":"更新个人资料","authRule":"/Api/Admin/Account/updatePersonal","authIcon":"","authType":1}]},"systemTimestamp":1687952668,"systemDateTime":"2023-06-28 19:44:28","msg":"登录成功"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":"用户名或密码错误"})
     */
    public function login()
    {
        $param = $this->request()->getRequestParam();

        try {
            \EasySwoole\ORM\DbManager::getInstance()->startTransactionWithCount();
            $runMode = Core::getInstance()->runMode();
            if ($runMode != 'dev') {
                //Func::checkVerifyCodeFunc($param['verifyUniqueId'], $param['imgCode'], VerifyCodeType::ADMIN_LOGIN);
            }

            $admin = LoginService::getInstance()->login($param['adminAccount'], $param['adminPassword']);

            // 2023-12-29 增加google验证
            if ($runMode != 'dev' && $admin->googleAuthenticatorSecret) {
                $ga = new PHPGangsta_GoogleAuthenticator();
                $result = $ga->verifyCode($admin->googleAuthenticatorSecret, $param['safeCode']);

                if (!$result) {
                    throw new Exception('动态口令错误', Status::CODE_BAD_REQUEST);
                }
            }

            $authList = $admin->getAuth();
            if (!$authList) {
                throw new \Exception('用户权限异常', Status::CODE_BAD_REQUEST);
            }

            $ip = ip2long($this->clientRealIP());
            $admin->update([
                'lastLoginIpLong' => $ip,
                'lastLoginTime' => date('Y-m-d H:i:s'),
            ]);

            AdminLogsModel::create([
                'adminId' => $admin->adminId,
                'action' => $this->request()->getUri()->getPath(),
                'requestIp' => $ip,
                'type' => AdminLogsModel::TYPE_ADD,
                'authId' => '-1',
                'logModule' => '登录',
                'status' => 1,
                'content' => '登录成功' . "(账号：$admin->adminAccount)"
            ])->save();

            \EasySwoole\ORM\DbManager::getInstance()->commitWithCount();

            $tokenData = [
                'adminId' => $admin->adminId,
                'merchantId' => $admin->merchantId,
                'adminType' => $admin->adminType,
                'adminAccount' => $admin->adminAccount,
                'adminNickname' => $admin->adminNickname,
            ];

            $token = $this->generateToken($tokenData, true);

            // 如果开启了只允许单设备登录则要将jwt和对应的用户id存入redis中当做session来使用
            $forceSingleDeviceLogin = ConfigModel::create()->getConfigValue(SystemConfigKey::FORCE_SINGLE_DEVICE_LOGIN);

            $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
            if ($forceSingleDeviceLogin) {
                $key = SystemRedisKey::session($admin->adminId, $admin->adminType);
                $expire = Config::getInstance()->getConf('ADMIN_JWT.expire');
                $redis->setEx($key, $expire, $token);
            }

        } catch (\Throwable $e) {
            \EasySwoole\ORM\DbManager::getInstance()->rollbackWithCount();

            $admin = AdminModel::create()->get(['adminAccount' => $param['adminAccount']]);
            $ip = ip2long($this->clientRealIP());

            // 要确保有这个账号才操作，如果没有就无视了。
            if ($admin) {
                /* 这里事务关闭后，进行封禁操作 begin */
                $loginFailureLimitKey = SystemRedisKey::loginFailureLimit($param['adminAccount'], UserType::TYPE_SYSTEM);

                $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
                $count = $redis->get($loginFailureLimitKey);
                if ($count >= 5) {
                    AdminModel::create()
                        ->where(['adminAccount' => $param['adminAccount']])
                        ->update(['status' => AdminModel::STATE_FORBIDDEN]);

                    AdminLogsModel::create([
                        'adminId' => $admin->adminId,
                        'type' => AdminLogsModel::TYPE_SELECT,
                        'logModule' => '登录',
                        'action' => $this->request()->getUri()->getPath(),
                        'authId' => '-1',
                        'status' => 0,
                        'content' => "密码错误次数过多自动封禁。(账号：$admin->adminAccount)",
                        'requestIp' => $ip,
                    ])->save();
                } else {
                    AdminLogsModel::create([
                        'adminId' => $admin->adminId,
                        'type' => AdminLogsModel::TYPE_SELECT,
                        'logModule' => '登录',
                        'action' => $this->request()->getUri()->getPath(),
                        'authId' => '-1',
                        'status' => 0,
                        'content' => "当前密码连续错误次数：{$count}。(账号：$admin->adminAccount)",
                        'requestIp' => $ip,
                    ])->save();
                }
                /* 这里事务关闭后，进行封禁操作 end */
            }

            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [
            'token' => $token, //返回token
            'userInfo' => [
                'adminId' => $admin->adminId,
                'merchantId' => $admin->merchantId,
                'adminAccount' => $admin->adminAccount,
                'adminNickname' => $admin->adminNickname,
                'avatar' => $admin->avatar,
                'adminType' => $admin->adminType,
                'adminEmail' => $admin->adminEmail,
                'adminMobile' => $admin->adminMobile,
                'isGoogleAuthenticator' => !empty($admin->googleAuthenticatorSecret) ? 1 : 0,
            ],
            'navigation' => ConfigModel::create()->getConfigValueList(NavigationConfigKey::ALL_KEY),
            'authList' => $authList,
        ], '登录成功');
    }
}