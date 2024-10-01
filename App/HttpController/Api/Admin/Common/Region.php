<?php

namespace App\HttpController\Api\Admin\Common;

use App\HttpController\Api\Admin\AdminBase;
use App\Service\Region\RegionService;
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
use Throwable;


/**
 * Class Region
 * @package App\HttpController\Api\Admin\Common
 * @ApiGroup(groupName="后台-公共-地区 Admin/Common/Region")
 * @ApiGroupDescription("地区相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Region extends AdminBase
{
    /**
     * 省市两级列表
     * @Api(name="省市两级列表",path="/Api/Admin/Common/Region/provinceCityList")
     * @ApiDescription("省市两级列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess()
     */
    public function provinceCityList()
    {
        $param = $this->request()->getRequestParam();

        try {

            $data = RegionService::getInstance()->getProvinceCityList();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}