<?php

namespace App\HttpController\Api\Admin\System;

use App\Enum\ConfigKey\NavigationConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Common\ConfigModel;
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

/**
 * Class Navigation
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-导航 Admin/System/Navigation")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Navigation extends AdminBase
{
    /**
     * 获取导航配置列表
     * @Api(name="获取导航配置列表",path="/Api/Admin/System/Navigation/configList")
     * @ApiDescription("获取导航配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"cfgKey":"MerchantBalanceReminder","cfgValue":"1","title":"商户余额提醒开关","description":"是否开启余额不足提醒 1.是 0.否"},{"cfgKey":"ReminderAmount","cfgValue":"200","title":"商户余额提醒金额","description":"低于这个数就会提醒"},{"cfgKey":"ReminderFrequency","cfgValue":"60","title":"商户余额提醒频率","description":"单位秒"}],"systemTimestamp":1687952087,"systemDateTime":"2023-06-28 19:34:47","msg":"OK"})
     */
    public function configList()
    {
        try {
            $result = ConfigModel::create()
                ->field(['cfgKey', 'cfgValue', 'title', 'description'])
                ->where(['cfgKey' => [NavigationConfigKey::ALL_KEY, 'IN']])
                ->all();

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 设置导航配置
     * @Api(name="设置导航配置",path="/Api/Admin/System/Navigation/setConfig")
     * @ApiDescription("设置导航配置")
     * @Method(allow=["POST"])
     * @Param(name="MerchantBalanceReminder", alias="商户余额提醒开关", type="int", optional="", inArray=[1, 0], description="是否开启余额不足提醒 1.是 0.否")
     * @Param(name="ReminderAmount", alias="商户余额提醒金额", type="int", optional="", min="1", description="低于这个数就会提醒")
     * @Param(name="ReminderFrequency", alias="商户余额提醒频率", type="int", optional="", min="30", description="单位秒")
     * @Param(name="SingleIpDailyClickLimit", alias="单个IP广告每日点击次数统计限制", type="int", optional="", min="0", description="0为不限制")
     * @ApiSuccess({"code":200,"result":4,"systemTimestamp":1685500974,"systemDateTime":"2023-05-31 10:42:54","msg":"OK"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['MerchantBalanceReminder']) && $data['MerchantBalanceReminder'] = intval($param['MerchantBalanceReminder']);
            isset($param['ReminderAmount']) && $data['ReminderAmount'] = $param['ReminderAmount'];
            isset($param['ReminderFrequency']) && $data['ReminderFrequency'] = $param['ReminderFrequency'];
            isset($param['SingleIpDailyClickLimit']) && $data['SingleIpDailyClickLimit'] = intval($param['SingleIpDailyClickLimit']);

            if (!$data) {
                throw new \Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            $result = ConfigModel::create()->setConfig($data);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
}