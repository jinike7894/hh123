<?php

namespace App\HttpController\Api\Admin;

use App\Enum\AccountStatus;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\Enum\UserType;
use App\Model\Admin\AdminLogsModel;
use App\Model\Admin\AdminModel;
use App\Model\Common\ConfigModel;
use App\RedisKey\SystemRedisKey;
use App\Utility\Func;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\Hash;
use PHPGangsta_GoogleAuthenticator;
use Exception;
use Throwable;

/**
 * Class Account
 * @package App\HttpController
 * @ApiGroup(groupName="后台-账号管理 Admin/Account")
 * @ApiGroupDescription("后台管理员账号相关")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Account extends AdminBase
{

    /**
     * 更新个人信息
     * @Api(name="更新个人信息",path="/Api/Admin/Account/updatePersonal")
     * @Method(allow=["POST"])
     * @Param(name="oldAdminPassword", alias="用户原密码", type="string", optional="", lengthMin="6", lengthMax="20", description="用户原密码")
     * @Param(name="adminPassword", alias="用户密码",type="string", optional="", lengthMin="6", lengthMax="20", description="用户密码")
     * @Param(name="adminNickname", alias="用户昵称",type="string", optional="", mbLengthMax="12", description="用户昵称")
     * @Param(name="adminEmail", alias="email", type="string", optional="", lengthMax="40", description="邮箱")
     * @Param(name="phoneNumber", alias="手机号", type="string", optional="", lengthMax="20", description="手机号", regex="/^1[3456789]\d{9}$/")
     * @Param(name="avatar", alias="用户头像", type="string", optional="", description="用户头像")
     * @ApiSuccess({"code":200,"result":{"adminId":3,"parentAdminId":0,"roleId":"1","merchantId":0,"adminNickname":"tommy","adminAccount":"tommy","adminType":"System","avatar":"","adminEmail":"","adminMobile":"","lastLoginIpLong":2886860801,"lastLoginTime":"2023-05-26 19:11:44","status":1,"googleAuthenticatorSecret":"","createTime":"2023-05-26 17:24:20","updateTime":"2023-05-26 19:13:59"},"systemTimestamp":1685099639,"systemDateTime":"2023-05-26 19:13:59","msg":"更新个人信息成功(tommy)"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":""})
     */
    public function updatePersonal()
    {
        $param = $this->request()->getRequestParam();
        try {
            \EasySwoole\ORM\DbManager::getInstance()->startTransactionWithCount();

            $adminInfo = AdminModel::create()->get(['adminId' => $this->who['adminId']]);
            if (empty($adminInfo)) {
                throw new \Exception('该管理员数据不存在', Status::CODE_BAD_REQUEST);
            }
            $updateData = [];

            $updateData['adminId'] = $this->who['adminId'];
            if (!empty($param['adminPassword']) && !empty($param['oldAdminPassword'])) {
                if ($param['adminPassword'] == $param['oldAdminPassword']) {
                    throw new \Exception('原密码不能和新设密码一样', Status::CODE_BAD_REQUEST);
                }

                if (!Hash::validatePasswordHash($param['oldAdminPassword'], $adminInfo->adminPassword)) {
                    throw new \Exception('用户原密码错误', Status::CODE_BAD_REQUEST);
                }
                $updateData['adminPassword'] = Hash::makePasswordHash($param['adminPassword']);
            }

            if (!empty($param['adminNickname'])) {
                $adminNickname = AdminModel::create()
                    ->where(['adminNickname' => $param['adminNickname']])
                    ->where('adminId', $this->who['adminId'], '!=')
                    ->get();
                if ($adminNickname) {
                    throw new \Exception('用户昵称已存在', Status::CODE_BAD_REQUEST);
                }

                $updateData['adminNickname'] = $param['adminNickname'] ?? $adminInfo->adminNickname;
            }

            $updateData['avatar'] = $param['avatar'] ?? $adminInfo->avatar;
            $updateData['adminEmail'] = $param['adminEmail'] ?? $adminInfo->adminEmail;
            $updateData['adminMobile'] = $param['phoneNumber'] ?? $adminInfo->adminMobile;

            $adminInfo->update($updateData);

            if (\EasySwoole\ORM\DbManager::getInstance()->commitWithCount()) {
                return $this->writeJson(Status::CODE_OK, $adminInfo->hidden(['adminPassword']), "更新个人信息成功({$adminInfo->adminAccount})", AdminLogsModel::TYPE_UPDATE);
            } else {
                return $this->writeJson(Status::CODE_BAD_REQUEST, [], "更新个人信息失败({$adminInfo->adminAccount}):" . $adminInfo->lastQueryResult()->getLastError(), AdminLogsModel::TYPE_UPDATE);
            }
        } catch (\Throwable $e) {
            \EasySwoole\ORM\DbManager::getInstance()->rollback();
            return $this->writeJson($e->getCode(), [], $e->getMessage(), AdminLogsModel::TYPE_UPDATE);
        }
    }

    /**
     * 说一下google验证的内容
     * 1.prepareGoogleAuthenticator 准备google验证
     * 2.bindGoogleAuthenticator 绑定google验证
     * 3.unbindGoogleAuthenticator 解绑google验证
     *
     * 准备google验证器
     * @Api(name="准备google验证器",path="/Api/Admin/Account/prepareGoogleAuthenticator")
     * @ApiDescription("准备google验证器")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"qrCodeUrl":"https://api.qrserver.com/v1/create-qr-code/?data=otpauth%3A%2F%2Ftotp%2Ftommy%3Fsecret%3DYNDIMFXGJUQGUKS4%26issuer%3D&size=200x200&ecc=M","secret":"YNDIMFXGJUQGUKS4"},"systemTimestamp":1703833756,"systemDateTime":"2023-12-29 15:09:16","msg":"OK"})
     */
    public function prepareGoogleAuthenticator()
    {
        $param = $this->request()->getRequestParam();

        try {
            $admin = AdminModel::create()->get($this->who['adminId']);

            if (!empty($admin->googleAuthenticatorSecret)) {
                throw new \Exception('当前用户已经绑定google验证', Status::CODE_BAD_REQUEST);
            }

            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret = $ga->createSecret();
            $qrCodeUrl = $ga->getQRCodeGoogleUrl($admin->adminAccount, $secret, '');
            $data = ['qrCodeUrl' => $qrCodeUrl, 'secret' => $secret];

        } catch (\Throwable $msg) {
            $this->writeJson(Status::CODE_BAD_REQUEST, [], $msg->getMessage());
        }

        $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 验证绑定google验证器
     * @Api(name="验证绑定google验证器",path="/Api/Admin/Account/bindGoogleAuthenticator")
     * @ApiDescription("验证绑定google验证器")
     * @Method(allow=["POST"])
     * @Param(name="safeCode", alias="google验证码", type="string", required="", mbLengthMin="1", description="google验证码")
     * @Param(name="secret", alias="秘钥",type="string", required="", mbLengthMin="1", description="秘钥")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function bindGoogleAuthenticator()
    {
        $param = $this->request()->getRequestParam();

        try {
            $safeCode = $param['safeCode'] ?? '';
            $secret = $param['secret'] ?? '';

            $ga = new PHPGangsta_GoogleAuthenticator();
            $admin = AdminModel::create()->get($this->who['adminId']);
            if (!$admin || !empty($admin->googleAuthenticatorSecret)) {
                throw new Exception('该数据不存在或已绑定谷歌验证', Status::CODE_BAD_REQUEST);
            }

            $result = $ga->verifyCode($secret, $safeCode);

            if (!$result) {
                throw new Exception('动态口令错误', Status::CODE_BAD_REQUEST);
            }

            if (!$admin->update(['googleAuthenticatorSecret' => $secret])) {
                throw new Exception('绑定异常,稍后重试', Status::CODE_INTERNAL_SERVER_ERROR);
            }

        } catch (Throwable $e) {
            $this->writeJson($e->getCode(), '', $e->getMessage());
        }

        $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK), AdminLogsModel::TYPE_UPDATE);
    }

    /**
     * 解绑google验证码
     * @Api(name="解绑google验证码",path="/Api/Admin/Account/unbindGoogleAuthenticator")
     * @ApiDescription("解绑google验证码（这个是解除自己账号google验证的方法）")
     * @Method(allow=["POST"])
     * @Param(name="safeCode", alias="google验证码", type="string", required="", mbLengthMin="1", description="google验证码")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function unbindGoogleAuthenticator()
    {
        $param = $this->request()->getRequestParam();

        try {
            $safeCode = $param['safeCode'] ?? '';

            $ga = new PHPGangsta_GoogleAuthenticator();
            $admin = AdminModel::create()->get($this->who['adminId']);
            if (!$admin || empty($admin->googleAuthenticatorSecret)) {
                throw new Exception('该账号数据不存在或没有绑定google验证', Status::CODE_BAD_REQUEST);
            }

            $result = $ga->verifyCode($admin->googleAuthenticatorSecret, $safeCode);
            if (!$result) {
                throw new Exception('动态口令错误', Status::CODE_BAD_REQUEST);
            }

            if (!$admin->update(['googleAuthenticatorSecret' => ''])) {
                throw new \Exception('绑定异常,稍后重试', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $logStr = "--解绑用户:({$admin->adminAccount})：google验证";
        } catch (\Throwable $e) {
            $this->writeJson($e->getCode(), '', $e->getMessage());
        }

        $this->writeJson(Status::CODE_OK, $result, "解绑成功", AdminLogsModel::TYPE_UPDATE, $logStr);
    }

    #################### 下面的都是旧的，暂时放在那，如果修改完毕，请放到上面来。 ####################

    /**
     * 管理员列表
     * @Api(name="管理员列表",path="/Api/Admin/Account/adminList")
     * @ApiDescription("管理员列表")
     * @Method(allow=["GET","POST"])
     * @Param(name="adminId", alias="管理员ID", type="int",optional="", description="管理员ID")
     * @Param(name="adminAccount", alias="管理员账号", type="string",optional="", description="管理员账号")
     * @Param(name="adminType", alias="管理员类型", type="string",optional="", description="管理员类型(System-管理员,Merchant-商户)")
     * @Param(name="status", alias="状态", type="int", optional="",description="状态(用户状态 0禁用 1正常 2全部)")
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="keyword", alias="关键字", type="string", description="关键字")
     * @apiSuccess({"code":200,"result":{"total":3,"list":[{"adminId":6,"adminNickname":"admin","adminAccount":"admin","adminType":"system","merchantId":0,"adminEmail":"","adminMobile":"","roleId":1,"status":1,"createTime":"2022-02-21
     * 16:13:17","updateTime":"2022-02-21 16:13:17"}]},"systemTimestamp":1645439905,"systemDateTime":"2022-02-21
     * 18:38:25","msg":"OK"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":""})
     */
    public function adminList()
    {
        $param = $this->request()->getRequestParam();
        $options = [
            'adminId' => $param['adminId'] ?? '',
            'adminAccount' => !empty($param['adminAccount']) ? trim($param['adminAccount']) : '',
            'adminType' => !empty($param['adminType']) ? trim($param['adminType']) : '',
            'status' => $param['status'] ?? 2,
        ];

        $page = (int)($param['page'] ?? 1);
        $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

        $data = AdminModel::create()->getAll($page, $options, $pageSize);
        $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * # TODO: 只是放在这还没用
     * 添加管理员用户（类型可选商户）
     * @Api(name="添加管理员用户",path="/Api/Admin/Account/add")
     * @ApiDescription("添加管理员用户")
     * @Method(allow=["POST"])
     * @Param(name="adminPassword", alias="用户密码",type="string", required="",lengthMin="6", lengthMax="20",description="用户密码",regex="/^(?![^a-zA-Z]+$)(?!\D+$)/")
     * @Param(name="adminNickname", alias="用户昵称",type="string", optional="",lengthMax="20",description="用户昵称")
     * @Param(name="adminAccount", alias="用户登录名",type="string", required="", lengthMax="20",description="用户登录名")
     * @Param(name="adminType", alias="用户类型",type="string", required="",lengthMax="20",description="用户类型（System-管理员,Merchant-商户）")
     * @Param(name="adminEmail", alias="email",type="string",optional="",lengthMax="40",description="邮箱")
     * @Param(name="adminMobile", alias="手机号",type="string",optional="",lengthMax="11",description="手机号")
     * @Param(name="roleId", type="int",required="",integer="",description="角色id(1,2,3....)")
     * @Param(name="status",type="int",integer="",description="用户状态（-1删除 0禁用 1正常）")
     * @ApiSuccess({"code":200,"result":{"adminPassword":"$2y$10$V7HW3Y/5H.8T3H0ijxj9IuYUIAP4ST7S3wk2I8PjOIJUwN.Jwl1Zq","adminNickname":"admin","adminAccount":"admin3","adminType":"system","adminEmail":"","adminMobile":"","roleId":"1","auth":"","status":"1","createTime":"2022-02-21 16:13:17","updateTime":"2022-02-21 16:13:17","adminId":6},"systemTimestamp":1645431197,"systemDateTime":"2022-02-21 16:13:17","msg":"OK"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":""})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();
        $adminType = $param['adminType'] ?? UserType::TYPE_SYSTEM;
        try {
            \EasySwoole\ORM\DbManager::getInstance()->startTransactionWithCount();

            if (!in_array($adminType, [UserType::TYPE_SYSTEM, UserType::TYPE_MERCHANT])) {
                throw new \Exception('用户类型错误(System-管理员,Merchant-商户)', Status::CODE_BAD_REQUEST);
            }
            $adminInfo = AdminModel::create()->get(['adminAccount' => $param['adminAccount']]);
            if ($adminInfo) {
                throw new \Exception('当前用户名已存在', Status::CODE_BAD_REQUEST);
            }
            $adminNickname = AdminModel::create()->get(['adminNickname' => $param['adminNickname']]);

            if ($adminNickname) {
                throw new \Exception('当前昵称已存在', Status::CODE_BAD_REQUEST);
            }

            if ($adminType == UserType::TYPE_MERCHANT) {
                $lastMerchant = MerchantModel::create()->where(['agentLevel' => 0])->order('merchantId', 'DESC')->get();
                if (!empty($lastMerchant)) {
                    $agentLeft = $lastMerchant->agentRight + 1;
                    $agentRight = $lastMerchant->agentRight + 2;
                    //更新后续节点值
                    MerchantModel::create()->updateAgentLeftRight($agentLeft);
                } else {
                    $merchant = MerchantModel::create()->where(['agentLevel' => 0])->get();
                    $agentLeft = $merchant->agentLeft + 1;
                    $agentRight = $merchant->agentLeft + 2;
                    //更新后续节点值
                    MerchantModel::create()->updateAgentLeftRight($agentLeft);
                }
                $depositKey = $withdrawKey = Func::CreateGuid();
                //添加用户成功 根据用户类型判断是否生成商户列表
                $dataMerchant = [
                    'merchantNo' => Func::CreateGuid('-'),
                    'merchantName' => $param['adminAccount'],
                    'walletAddress' => Func::CreateGuid(),
                    'securePassword' => !empty($param['securePassword']) ? Hash::makePasswordHash($param['securePassword']) : '',
                    'depositKey' => $depositKey ?? '',
                    'withdrawKey' => $withdrawKey ?? '',
                    'agentLevel' => 0,
                    'agentLeft' => $agentLeft,
                    'agentRight' => $agentRight,
                    'status' => AccountStatus::STATE_NORMAL
                ];
                $merchantId = MerchantModel::create($dataMerchant)->save();
                //暂时用户管理员名昵称或账号
                //MerchantModel::create()->where(['merchantId' => $merchantId])->update(['merchantName' => $merchantId . '号商户']);
            }

            $data = [
                'merchantId' => $merchantId ?? 0,
                'adminPassword' => Hash::makePasswordHash($param['adminPassword']),
                'adminNickname' => $param['adminNickname'] ?? $param['adminAccount'],
                'adminAccount' => $param['adminAccount'] ?? '',
                'adminType' => $param['adminType'] ?? UserType::TYPE_SYSTEM,
                'adminEmail' => $param['adminEmail'] ?? '',
                'adminMobile' => $param['adminMobile'] ?? '',
                'roleId' => empty($merchantId) ? 3 : $param['roleId'],
                'status' => $param['status'] ?? AccountStatus::STATE_NORMAL,
            ];

            $adminId = AdminModel::create($data)->save();

            // 初始化信用积分
            CreditModel::create(['whoId' => $adminId, 'whoType' => $param['adminType']])->getItem();

            \EasySwoole\ORM\DbManager::getInstance()->commitWithCount();
        } catch (\Throwable $msg) {
            \EasySwoole\ORM\DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson(Status::CODE_BAD_REQUEST, [], $msg->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $adminId, "管理员添加成功({$data['adminAccount']})");
    }

    /**
     * @Api(name="用户编辑",path="/Api/Admin/Account/update")
     * @Method(allow=["POST"])
     * @Param(name="adminId", alias="管理员id",type="int", required="",description="管理员id")
     * @Param(name="adminPassword", alias="用户密码",type="string", optional="",lengthMin="6", lengthMax="20",description="用户密码",regex="/^(?![^a-zA-Z]+$)(?!\D+$)/")
     * @Param(name="adminAccount", alias="用户名",type="string", optional="",lengthMax="20",description="用户名")
     * @Param(name="adminNickname", alias="用户昵称",type="string", optional="",mbLengthMax="12",description="用户昵称")
     * @Param(name="adminEmail", alias="email",type="string",optional="",lengthMax="40",description="邮箱")
     * @Param(name="adminMobile", alias="手机号",type="string",optional="",length="30",description="手机号")
     * @Param(name="avatar", alias="用户头像",type="string",optional="",description="用户头像")
     * @Param(name="roleId", optional="",type="int",integer="",description="角色id")
     * @Param(name="status", optional="",type="int",integer="",description="用户状态（-1删除 0禁用 1正常）")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1645440353,"systemDateTime":"2022-02-21 18:45:53","msg":"OK"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":""})
     */
    public function update()
    {
        $param = $this->request()->getRequestParam();
        try {
            \EasySwoole\ORM\DbManager::getInstance()->startTransactionWithCount();

            $adminInfo = AdminModel::create()->get(['adminId' => $param['adminId']]);
            if (empty($adminInfo)) {
                throw new \Exception('该管理员数据不存在', Status::CODE_BAD_REQUEST);
            }
            $updateData = [];

            $updateData['adminId'] = $param['adminId'] ?? $adminInfo->adminId;
            if (!empty($param['adminPassword'])) {
                $updateData['adminPassword'] = password_hash($param['adminPassword'], PASSWORD_DEFAULT);
            }
            //判断用户名是否存在
            if (!empty($param['adminAccount'])) {
                $admins = AdminModel::create()->where(['adminAccount' => $param['adminAccount']])->where('adminId', $param['adminId'], '!=')->get();
                if ($admins) {
                    throw new \Exception('用户名已存在', Status::CODE_BAD_REQUEST);
                }
            }

            if (!empty($param['adminNickname'])) {
                $admins = AdminModel::create()->where(['adminNickname' => $param['adminNickname']])->where('adminId', $param['adminId'], '!=')->get();
                if ($admins) {
                    throw new \Exception('用户昵称已存在', Status::CODE_BAD_REQUEST);
                }
            }

            $updateData['adminAccount'] = $param['adminAccount'] ?? $adminInfo->adminAccount;
            $updateData['adminNickname'] = $param['adminNickname'] ?? $adminInfo->adminNickname;
            $updateData['roleId'] = $param['roleId'] ?? $adminInfo->roleId;
            $updateData['status'] = $param['status'] ?? $adminInfo->status;
            $updateData['avatar'] = $param['avatar'] ?? $adminInfo->avatar;
            $updateData['adminEmail'] = $param['adminEmail'] ?? $adminInfo->adminEmail;
            $updateData['adminMobile'] = $param['adminMobile'] ?? $adminInfo->adminMobile;

            $rs = $adminInfo->update($updateData);
            if (!$rs) {
                throw new \Exception($adminInfo->lastQueryResult()->getLastError(), Status::CODE_BAD_REQUEST);
            }

            // 如果是禁用或者删除操作，尝试清除token，只有在开启强制单设备登录的情况下有效。
            if (isset($param['status']) && $param['status'] < 1) {
                $forceSingleDeviceLogin = ConfigModel::create()->getConfigValue(SystemConfigKey::FORCE_SINGLE_DEVICE_LOGIN);
                if ($forceSingleDeviceLogin) {
                    $redisAuth = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
                    $key = SystemRedisKey::session($adminInfo->adminId, $adminInfo->adminType);
                    $redisAuth->del($key);
                }
            }

            \EasySwoole\ORM\DbManager::getInstance()->commitWithCount();
        } catch (\Throwable $msg) {
            \EasySwoole\ORM\DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson(Status::CODE_BAD_REQUEST, [], $msg->getMessage(), AdminLogsModel::TYPE_UPDATE);
        }
        !empty($param['adminPassword']) && $param['adminPassword'] = '******';
        return $this->writeJson(Status::CODE_OK, [], "更新成功({$adminInfo->adminAccount})", AdminLogsModel::TYPE_UPDATE, json_encode($updateData));
    }

    /**
     * @Api(name="管理员详情",path="/Api/Admin/Account/getOne")
     * @Method(allow=["GET","POST"])
     * @ApiDescription("管理员详情")
     * @Param(name="adminId", alias="用户id", type="int", required="",integer="",description="用户id")
     * @apiSuccess({"code":200,"result":{"admin":{"adminId":2,"parentAdminId":0,"merchantId":1,"adminNickname":"1号商户","adminAccount":"sh1","adminType":"Merchant","adminEmail":"","adminMobile":"","lastLoginIpLong":2886926337,"lastLoginTime":"2022-03-16 18:29:19","status":1,"createTime":"1000-01-01 00:00:00","updateTime":"2022-03-16 18:29:19"},"role":[{"roleId":2,"roleName":"测试组1","createTime":"1000-01-01 00:00:00","updateTime":"1000-01-01 00:00:00"},{"roleId":3,"roleName":"测试组2","createTime":"1000-01-01 00:00:00","updateTime":"1000-01-01 00:00:00"}]},"systemTimestamp":1647427075,"systemDateTime":"2022-03-16 18:37:55","msg":"OK"})
     * @apiFail({"code":400,"result":[],"systemTimestamp":1645600237,"systemDateTime":"2022-02-23 15:10:37","msg":"用户id必须填写"})
     */
    public function getOne()
    {
        $param = $this->request()->getRequestParam();

        try {
            $result = AdminModel::create()->getOne($param['adminId']);
        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 获取登录用户详情
     * @Api(name="管理员详情",path="/Api/Admin/Account/getOne")
     * @Method(allow=["GET","POST"])
     * @ApiDescription("获取登录用户详情")
     * @apiSuccess({"code":200,"result":{"admin":{"adminId":2,"parentAdminId":0,"merchantId":1,"adminNickname":"1号商户","adminAccount":"sh1","adminType":"Merchant","adminEmail":"","adminMobile":"","lastLoginIpLong":2886926337,"lastLoginTime":"2022-03-16 18:29:19","status":1,"createTime":"1000-01-01 00:00:00","updateTime":"2022-03-16 18:29:19"},"role":[{"roleId":2,"roleName":"测试组1","createTime":"1000-01-01 00:00:00","updateTime":"1000-01-01 00:00:00"},{"roleId":3,"roleName":"测试组2","createTime":"1000-01-01 00:00:00","updateTime":"1000-01-01 00:00:00"}]},"systemTimestamp":1647427075,"systemDateTime":"2022-03-16 18:37:55","msg":"OK"})
     * @apiFail({"code":400,"result":[],"systemTimestamp":1645600237,"systemDateTime":"2022-02-23 15:10:37","msg":"用户id必须填写"})
     */
    public function getDetail()
    {
        try {
            if (!empty($this->who['merchantId'])) {
                $result = AdminModel::create()->getOne($this->who['adminId']);
            } else {
                $result = AdminModel::create()->get($this->who['adminId']);
                $result->hidden([
                    'adminPassword',
                    'googleAuthenticatorSecret',
                    'lastLoginIpLong',
                ]);
            }
        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 验证google验证码
     * @Api(name="验证google验证码",path="/Api/Admin/Account/checkGoogleAuthenticator")
     * @Method(allow=["POST"])
     * @Param(name="adminId", alias="管理员id",type="int", optional="",description="管理员id")
     * @Param(name="adminAccount", alias="账号",type="string", optional="",description="账号")
     * @Param(name="safeCode", alias="google验证码",type="string", required="",description="google验证码")
     * @ApiSuccess({"code":200,"result":"469f71e4d3ad5128e069a1a47ac4837a","systemTimestamp":1646030059,"systemDateTime":"2022-02-28 14:34:19","msg":"OK"})
     * @apiFail({"code":400,"result":"","systemTimestamp":1645439577,"systemDateTime":"2022-02-21 18:32:57","msg":"Bad Request"})
     */
    public function checkGoogleAuthenticator()
    {
        $param = $this->request()->getRequestParam();
        $safeCode = $param['safeCode'] ?? '';
        $adminAccount = $param['adminAccount'] ?? '';
        $adminId = $param['adminId'] ?? '';
        try {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $admin = AdminModel::create()->where("(adminAccount = ? OR adminId = ?)", [$adminAccount, $adminId])->get();
            if (!$admin || empty($admin->googleAuthenticatorSecret)) {
                throw new \Exception('该账号数据不存在或没有绑定google验证', Status::CODE_BAD_REQUEST);
            }
            $result = $ga->verifyCode($admin->googleAuthenticatorSecret, $safeCode, 1);
            if (!$result) {
                throw new \Exception('动态口令错误', Status::CODE_BAD_REQUEST);
            }
            $this->writeJson(Status::CODE_OK, '', "验证码成功", AdminLogsModel::TYPE_UPDATE);
        } catch (\Throwable $e) {
            $this->writeJson($e->getCode(), '', $e->getMessage());
        }
    }


}
