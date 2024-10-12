<?php

namespace App\HttpController\Api\Home;

use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\Model\Common\ConfigModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use Exception;
use Throwable;

/**
 * Class Config
 * @package App\HttpController\Api\Home
 * @ApiGroup(groupName="配置 Home/Config")
 * @ApiGroupDescription("配置相关的操作")
 */
class Config extends ApiBase
{
    /**
     * app配置
     * @Api(name="app配置",path="/Api/Home/Config/app")
     * @ApiDescription("app配置")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"AndroidDownloadUrl":"https://dl.mochaav.com","AndroidMinVersion":"1.0","AndroidVersion":"1.0","ApiDomain":["https://www.mochaav.com","https://api.mochaav.com"],"DownloadPageUrl":"https://dl.mochaav.com","H5PageUrl":"https://www.mochaav.com","IOSDownloadUrl":"","IOSMinVersion":"","IOSVersion":""},"systemTimestamp":1702380120,"systemDateTime":"2023-12-12 19:22:00","msg":"success"})
     */
    public function app()
    {
        $param = $this->request()->getRequestParam();

        try {

            $appConfig = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_APP);
            return $this->writeJson(Status::CODE_OK, $appConfig, 'success');
            // 接口域名需要转数组
            if (isset($appConfig[AppConfigKey::API_DOMAIN])) {
                $tempList = explode(';', $appConfig[AppConfigKey::API_DOMAIN]);

                $apiDomain = [];
                foreach ($tempList as $item) {
                    $item = trim($item);
                    $item && $apiDomain[] = $item;
                }

                $appConfig[AppConfigKey::API_DOMAIN] = $apiDomain;
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $appConfig, 'success');
    }
    
     /**
     * 记录落地页点击跳转
     * @Api(name="记录落地页点击跳转",path="/Api/Home/Config/mobileConfig")
     * @ApiDescription("记录落地页点击跳转，因为很多原因需要单独调用。1.导航只允许使用纯静态html 2.因是纯静态html，是否ip扣量要在前端判断")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelCode", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function mobileConfig()
    {
        $param = $this->request()->getRequestParam();

        try {

        $filePath = '/www/wwwroot/xingwen/esnavigation/Public/moblieconfig/' . $param['channelCode'] . '/itms-services.mobileconfig';
        if (file_exists($filePath)) {
            // 设置响应头
            $this->response()->sendFile($filePath);
            $this->response()->withHeader('Content-Description', 'File Transfer');
            $this->response()->withHeader('Content-Type', 'application/xml');
            $this->response()->withHeader('Content-Disposition', 'attachment; filename="' . basename($filePath) . '"');
            $this->response()->withHeader('Expires', '0');
            $this->response()->withHeader('Cache-Control', 'must-revalidate');
            $this->response()->withHeader('Pragma', 'public');
            $this->response()->withHeader('Content-Length', filesize($filePath));
            $this->response()->withStatus(200);
            $this->response()->end();
        } else {
            // 文件不存在时的处理
            return $this->writeJson(404, [], 'not found');
        }
           

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
    }
}