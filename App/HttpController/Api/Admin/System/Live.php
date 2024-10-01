<?php

namespace App\HttpController\Api\Admin\System;

use App\Enum\ConfigKey\LiveConfigKey;
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
 * Class Live
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-直播 Admin/System/Live")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Live extends AdminBase
{
    /**
     * 获取直播配置列表
     * @Api(name="获取直播配置列表",path="/Api/Admin/System/Live/configList")
     * @ApiDescription("获取直播配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"cfgKey":"Announcement","cfgValue":"直播公告","title":"直播公告","description":"直播公告"}],"systemTimestamp":1702565253,"systemDateTime":"2023-12-14 22:47:33","msg":"OK"})
     */
    public function configList()
    {
        try {
            $result = ConfigModel::create()
                ->field(['cfgKey', 'cfgValue', 'title', 'description'])
                ->where(['cfgKey' => [LiveConfigKey::ALL_KEY, 'IN']])
                ->all();

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 设置直播配置
     * @Api(name="设置直播配置",path="/Api/Admin/System/Live/setConfig")
     * @ApiDescription("设置直播配置")
     * @Method(allow=["POST"])
     * @Param(name="Announcement", alias="直播公告", type="string", optional="", description="直播公告")
     * @ApiSuccess({"code":200,"result":1,"systemTimestamp":1702565396,"systemDateTime":"2023-12-14 22:49:56","msg":"OK"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['Announcement']) && $data['Announcement'] = $param['Announcement'];

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