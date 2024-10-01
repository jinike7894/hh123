<?php

namespace App\HttpController\Api\Admin\Common;

use App\HttpController\Api\Admin\AdminBase;
use App\Enum\BalanceChangeType as BalanceChangeTypeEnum;
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
 * Class BalanceChangeType
 * @package App\HttpController\Api\Admin\Common
 * @ApiGroup(groupName="后台-公共-账变类型 Admin/Merchant/Merchant")
 * @ApiGroupDescription("后台账变类型相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class BalanceChangeType extends AdminBase
{
    /**
     * @Api(name="账变类型列表",path="/Api/Admin/Common/BalanceChangeType/getList")
     * @ApiDescription("账变类型列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"key":"ManualAdd","name":"调整加币"},{"key":"ManualReduce","name":"调整减币"},{"key":"Click","name":"点击计费"}],"systemTimestamp":1687783525,"systemDateTime":"2023-06-26 20:45:25","msg":"OK"})
     */
    public function getList(){

        try {
            $data = BalanceChangeTypeEnum::TYPE_ALL_LIST;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}