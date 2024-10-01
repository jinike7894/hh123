<?php

namespace App\HttpController\Api\Admin\System;

use App\Enum\ConfigKey\AppConfigKey;
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
 * Class App
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-导航 Admin/System/App")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class App extends AdminBase
{
    /**
     * 获取app配置列表
     * @Api(name="获取app配置列表",path="/Api/Admin/System/App/configList")
     * @ApiDescription("获取app配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"cfgKey":"AndroidVersion","cfgValue":"1.0","title":"安卓当前版本","description":"安卓当前版本"},{"cfgKey":"AndroidMinVersion","cfgValue":"1.0","title":"安卓最低支持版本","description":"安卓最低支持版本"},{"cfgKey":"AndroidDownloadUrl","cfgValue":"https://dl.mochaav.com","title":"安卓下载地址","description":"安卓下载地址"},{"cfgKey":"IOSVersion","cfgValue":"","title":"IOS当前版本","description":"IOS当前版本"},{"cfgKey":"IOSMinVersion","cfgValue":"","title":"IOS最低支持版本","description":"IOS最低支持版本"},{"cfgKey":"IOSDownloadUrl","cfgValue":"","title":"IOS下载地址","description":"IOS下载地址"},{"cfgKey":"ApiDomain","cfgValue":"https://www.mochaav.com","title":"接口地址列表","description":"接口地址列表"},{"cfgKey":"DownloadPageUrl","cfgValue":"https://dl.mochaav.com","title":"下载页地址","description":"下载页地址"}],"systemTimestamp":1693211732,"systemDateTime":"2023-08-28 16:35:32","msg":"OK"})
     */
    public function configList()
    {
        try {
            $result = ConfigModel::create()
                ->field(['cfgKey', 'cfgValue', 'title', 'description'])
                ->where(['cfgKey' => [AppConfigKey::ALL_KEY, 'IN']])
                ->all();

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 设置导航配置
     * @Api(name="设置导航配置",path="/Api/Admin/System/App/setConfig")
     * @ApiDescription("设置导航配置")
     * @Method(allow=["POST"])
     * @Param(name="AndroidVersion", alias="安卓当前版本", type="string", optional="", description="安卓当前版本")
     * @Param(name="AndroidMinVersion", alias="安卓最低支持版本", type="string", optional="", description="安卓最低支持版本")
     * @Param(name="AndroidDownloadUrl", alias="安卓下载地址", type="string", optional="", description="安卓下载地址")
     * @Param(name="IOSVersion", alias="IOS当前版本", type="string", optional="", description="IOS当前版本")
     * @Param(name="IOSMinVersion", alias="IOS最低支持版本", type="string", optional="", description="IOS最低支持版本")
     * @Param(name="IOSDownloadUrl", alias="IOS下载地址", type="string", optional="", description="IOS下载地址")
     * @Param(name="ApiDomain", alias="接口地址列表", type="string", optional="", description="接口地址列表")
     * @Param(name="DownloadPageUrl", alias="下载页地址", type="string", optional="", description="下载页地址")
     * @Param(name="H5PageUrl", alias="H5地址", type="string", optional="", description="H5地址")
     * @Param(name="AiVideoPlayUrl", alias="ai视频播放地址", type="string", optional="", description="ai视频播放地址")
     * @ApiSuccess({"code":200,"result":7,"systemTimestamp":1693209290,"systemDateTime":"2023-08-28 15:54:50","msg":"OK"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['AndroidVersion']) && $data['AndroidVersion'] = $param['AndroidVersion'];
            isset($param['AndroidMinVersion']) && $data['AndroidMinVersion'] = $param['AndroidMinVersion'];
            isset($param['AndroidDownloadUrl']) && $data['AndroidDownloadUrl'] = $param['AndroidDownloadUrl'];
            isset($param['IOSVersion']) && $data['IOSVersion'] = $param['IOSVersion'];
            isset($param['IOSMinVersion']) && $data['IOSMinVersion'] = $param['IOSMinVersion'];
            isset($param['IOSDownloadUrl']) && $data['IOSDownloadUrl'] = $param['IOSDownloadUrl'];
            isset($param['ApiDomain']) && $data['ApiDomain'] = $param['ApiDomain'];
            isset($param['DownloadPageUrl']) && $data['DownloadPageUrl'] = $param['DownloadPageUrl'];
            isset($param['H5PageUrl']) && $data['H5PageUrl'] = $param['H5PageUrl'];
            isset($param['AiVideoPlayUrl']) && $data['AiVideoPlayUrl'] = $param['AiVideoPlayUrl'];

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