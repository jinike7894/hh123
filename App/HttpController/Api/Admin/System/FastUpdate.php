<?php

namespace App\HttpController\Api\Admin\System;

use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Common\ConfigModel;
use App\Service\Admin\UpdateDomainService;
use App\Service\Navigation\PageService;
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
use Exception;
use Throwable;

/**
 * Class UpdateDomain
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-基本设置 Admin/System/UpdateDomain")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 */
class FastUpdate extends AdminBase
{
    /**
     * 批量更新域名
     * @Api(name="快速更新域名",path="/Api/Admin/System/FastUpdate/setConfig")
     * @ApiDescription("快速更新域名")
     * @Method(allow=["POST"])
     * @Param(name="cdnHost", alias="cdn", type="string", optional="", description="cdn")
     * @Param(name="downPageHost", alias="下载页地址", type="string", optional="", description="下载页地址")
     * @Param(name="h5Host", alias="H5地址", type="string", optional="", description="H5地址")
     * @Param(name="s3Host", alias="s3域名", type="string", optional="", description="s3域名")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1703742007,"systemDateTime":"2023-12-28 13:40:07","msg":"ok"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['cdnHost']) && $data['CDN'] = $param['cdnHost'];
            isset($param['downPageHost']) && $data['DownloadPageUrl'] = $param['downPageHost'];
            isset($param['h5Host']) && $data['H5PageUrl'] = $param['h5Host'];
            isset($param['s3Host']) && $data['AwsS3Host'] = $param['s3Host'];

            $data = array_filter($data, function($value) {
                return !empty($value);
            });

            $resCount = ConfigModel::create()->setConfig($data);
            $res = PageService::getInstance()->deleteTemplateCache([10]);
            $result = [
                'resCount' => $resCount,
                'deleteCache' =>  $res == 1 ? '缓存已清除' : '缓存未清除'
            ];
        } catch (Throwable $e) {
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