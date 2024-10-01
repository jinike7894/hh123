<?php

namespace App\HttpController\Api\Prostitute;

use App\HttpController\Api\User\UserBase;
use App\Model\Prostitute\ProstituteTypeModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\Mysqli\QueryBuilder;
use Exception;
use Throwable;

/**
 * Class ProstituteType
 * @package App\HttpController\Api\Prostitute
 * @ApiGroup(groupName="楼凤类型 Prostitute/ProstituteType")
 * @ApiGroupDescription("楼凤类型相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class ProstituteType extends UserBase
{

    /**
     * 楼凤类型列表
     * @Api(name="楼凤类型列表",path="/Api/Prostitute/ProstituteType/prostituteTypeList")
     * @ApiDescription("楼凤类型列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"prostituteTypeId":1,"title":"楼凤信息","typeKey":"Information"},{"prostituteTypeId":2,"title":"认证外围","typeKey":"Certified"},{"prostituteTypeId":3,"title":"包养入住","typeKey":"Kept"}],"systemTimestamp":1700215858,"systemDateTime":"2023-11-17 18:10:58","msg":"OK"})
     */
    public function prostituteTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $keyword['status'] = ProstituteTypeModel::STATE_NORMAL;

            $field = [
                'prostituteTypeId',
                'title',
                'typeKey',
            ];

            $data = ProstituteTypeModel::create()
                ->field($field)
                ->setKeyWord($keyword)
                ->order('sort', 'DESC')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}