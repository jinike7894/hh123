<?php

namespace App\HttpController\Api\Home;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\Model\Banner\BannerModel;
use App\Model\Common\ConfigModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageTemplateModel;
use App\RedisKey\Navigation\TemplateKey;
use App\Service\Merchant\AutoChannelService;
use App\Service\Navigation\AdService;
use App\Service\Navigation\PageService;
use App\Service\Navigation\PageViewService;
use App\Utility\Func;
use App\Utility\LogHandler;
use EasySwoole\EasySwoole\Config;
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
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;


class Banner extends ApiBase
{
    //前台获取轮播图
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'img_src',
                'url',
                'is_internal',
                'name',
                'sort',
                'create_at',
                'update_at',
                'status',
            ];
            $result = BannerModel::create()
                ->where(["status"=>1])
                ->where(["is_del"=>BannerModel::STATE_No])
                ->order("sort","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

   
}