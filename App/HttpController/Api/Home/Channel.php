<?php

namespace App\HttpController\Api\Home;

use App\HttpController\Api\ApiBase;
use App\Service\Merchant\ChannelService;
use EasySwoole\EasySwoole\Core;
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
 * Class Channel
 * @package App\HttpController\Api\Home
 * @ApiGroup(groupName="渠道 Home/Channel")
 * @ApiGroupDescription("渠道相关的操作")
 */
class Channel extends ApiBase
{
    /**
     * 渠道安装记录
     * @Api(name="渠道安装记录",path="/Api/Home/Channel/install")
     * @ApiDescription("渠道安装记录")
     * @Method(allow=["POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @Param(name="channelDomain", alias="渠道域名", type="string", optional="", mbLengthMin="1", description="渠道域名，有些域名为推广域名，对应了渠道key")
     * @Param(name="source", alias="来源", type="string", required="", inArray=["IOS", "IOSBookmark", "Android"], description="来源")
     * @Param(name="deviceId", alias="设备id", type="string", required="", mbLengthMin="1", description="设备id")
     * @Param(name="operatingSystem", alias="系统名", type="string", required="", mbLengthMin="1", description="系统名")
     * @Param(name="operatingSystemVersion", alias="系统版本", type="string", required="", mbLengthMin="1", description="系统版本")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692771864,"systemDateTime":"2023-08-23 14:24:24","msg":"success"})
     */
    public function install()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();

            // 这个是测试用的
            if (Core::getInstance()->runMode() == 'dev' && isset($param['ip'])) {
                $ip = $param['ip'];
            }

            // 有些域名为推广域名，对应了渠道key
            // 所有这里就变成了可选参数，但是两者必须二选一
            $param['channelKey'] = $param['channelKey'] ?? '';
            $param['channelDomain'] = $param['channelDomain'] ?? '';

            if (!$param['channelKey'] && !$param['channelDomain']) {
                throw new Exception('渠道key或渠道域名必须存在', Status::CODE_BAD_REQUEST);
            }

            $data = [
                'channelKey' => $param['channelKey'],
                'channelDomain' => $param['channelDomain'],
                'source' => $param['source'],
                'deviceId' => $param['deviceId'],
                'operatingSystem' => $param['operatingSystem'],
                'operatingSystemVersion' => $param['operatingSystemVersion'],
                'ip' => $ip,
                'ipLong' => ip2long($ip),
            ];

            $result = ChannelService::getInstance()->recordInstall($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }

    /**
     * 记录顶部下载按钮点击统计
     * @Api(name="记录顶部下载按钮点击统计",path="/Api/Home/Channel/downClick")
     * @ApiDescription("记录顶部下载按钮点击统计")
     * @Method(allow=["POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", required="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess()
     */
    public function downClick()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();
            $data = [
                'channelKey' => $param['channelKey'],
                'ip' => $ip,
            ];

            $result = ChannelService::getInstance()->recordDownBtn($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }
}