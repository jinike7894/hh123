<?php

namespace App\HttpController\Api\Admin\System;

use App\Enum\ConfigKey\OssConfigKey;
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
 * Class Oss
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-Oss Admin/System/Oss")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Oss extends AdminBase
{
    /**
     * 获取Oss配置列表
     * @Api(name="获取Oss配置列表",path="/Api/Admin/System/Oss/configList")
     * @ApiDescription("获取Oss配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"cfgKey":"AwsS3Enabled","cfgValue":"1","title":"亚马逊S3启用","description":"1.是 0.否"},{"cfgKey":"AwsS3AccessId","cfgValue":"AKIAV4O6TTMDHK4K7JEX","title":"亚马逊S3AccessID","description":"亚马逊S3AccessID"},{"cfgKey":"AwsS3AccessKey","cfgValue":"NtsQjABhOIv8hxdnY4JKXdgmfZ1JcPzMuwtyRJOO","title":"亚马逊S3AccessKey","description":"亚马逊S3AccessKey"},{"cfgKey":"AwsS3Endpoint","cfgValue":"https://s3.ap-east-1.amazonaws.com","title":"亚马逊S3端点","description":"亚马逊S3端点"},{"cfgKey":"AwsS3Region","cfgValue":"ap-east-1","title":"亚马逊S3地区","description":"亚马逊S3地区"},{"cfgKey":"AwsS3Bucket","cfgValue":"mocha-video","title":"亚马逊S3桶名","description":"亚马逊S3桶名"},{"cfgKey":"AwsS3Host","cfgValue":"https://mocha-video.s3.ap-east-1.amazonaws.com","title":"亚马逊S3域名","description":"亚马逊S3域名"}],"systemTimestamp":1701079129,"systemDateTime":"2023-11-27 17:58:49","msg":"OK"})
     */
    public function configList()
    {
        try {
            $result = ConfigModel::create()
                ->field(['cfgKey', 'cfgValue', 'title', 'description'])
                ->where(['cfgKey' => [OssConfigKey::ALL_KEY, 'IN']])
                ->all();

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 设置Oss配置
     * @Api(name="设置Oss配置",path="/Api/Admin/System/Oss/setConfig")
     * @ApiDescription("设置Oss配置")
     * @Method(allow=["POST"])
     * @Param(name="AwsS3Enabled", alias="亚马逊S3开关", type="int", optional="", inArray=[1, 0], description="是否开启 1.是 0.否")
     * @Param(name="AwsS3AccessId", alias="亚马逊S3AccessID", type="string", optional="", description="亚马逊S3AccessID")
     * @Param(name="AwsS3AccessKey", alias="亚马逊S3AccessKey", type="string", optional="", description="亚马逊S3AccessKey")
     * @Param(name="AwsS3Endpoint", alias="亚马逊S3端点", type="string", optional="", description="亚马逊S3端点")
     * @Param(name="AwsS3Region", alias="亚马逊S3地区", type="string", optional="", description="亚马逊S3地区")
     * @Param(name="AwsS3Bucket", alias="亚马逊S3桶名", type="string", optional="", description="亚马逊S3桶名")
     * @Param(name="AwsS3Host", alias="亚马逊S3域名", type="string", optional="", description="亚马逊S3域名")
     * @ApiSuccess({"code":200,"result":4,"systemTimestamp":1685500974,"systemDateTime":"2023-05-31 10:42:54","msg":"OK"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['AwsS3Enabled']) && $data['AwsS3Enabled'] = intval($param['AwsS3Enabled']);
            isset($param['AwsS3AccessId']) && $data['AwsS3AccessId'] = $param['AwsS3AccessId'];
            isset($param['AwsS3AccessKey']) && $data['AwsS3AccessKey'] = $param['AwsS3AccessKey'];
            isset($param['AwsS3Endpoint']) && $data['AwsS3Endpoint'] = $param['AwsS3Endpoint'];
            isset($param['AwsS3Region']) && $data['AwsS3Region'] = $param['AwsS3Region'];
            isset($param['AwsS3Bucket']) && $data['AwsS3Bucket'] = $param['AwsS3Bucket'];
            isset($param['AwsS3Host']) && $data['AwsS3Host'] = $param['AwsS3Host'];

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