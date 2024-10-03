<?php

namespace App\HttpController\Api\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserVideoRecordModel;
use App\Model\Video\TypeModel;
use App\Model\Video\VideoModel;
use App\RedisKey\Video\VideoKey;
use App\Service\Video\TypeService;
use App\Service\Video\VideoService;
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
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

/**
 * Class Video
 * @package App\HttpController\Api\Video
 * @ApiGroup(groupName="影视区 Video/Video")
 * @ApiGroupDescription("影视区相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Type extends UserBase
{

   //获取热门tag
    public function hot()
    {
        $param = $this->request()->getRequestParam();

        try {
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $data = TypeModel::create()
            ->where(["type_status"=>TypeModel::NODELETED])
            ->order("type_sort","desc")
            ->limit($pageSize)
            ->all();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

}