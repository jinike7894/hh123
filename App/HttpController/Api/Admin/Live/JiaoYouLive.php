<?php

namespace App\HttpController\Api\Admin\Live;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveTongChengModel;
use App\Model\Navigation\AdModel;
use App\Model\Prostitute\ProstituteModel;
use App\Service\Live\LiveService;
use App\Model\Navigation\AdGroupRelationModel;
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
use App\HttpController\Api\Admin\Upload as uploadNew;
use Exception;
use Throwable;

/**
 * Class Live
 * @package App\HttpController\Api\Admin\Live
 * @ApiGroup(groupName="后台-直播-直播 Admin/Live/Live")
 * @ApiGroupDescription("后台直播相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class JiaoYouLive extends AdminBase
{

    public function list()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            isset($param['id']) && $keyword['id'] = intval($param['id']);
            isset($param['status']) && $keyword['status'] = trim($param['status']);
            $field = [
                "*"
            ];
            $data = LiveTongChengModel::create()
                ->order("sort","desc")
                ->getAll($page, $keyword, $pageSize, $field);
                if($data["list"]){
                    foreach($data["list"] as $k=>$v){
                        $imgData=new uploadNew();
                        $data["list"][$k]->cover=$imgData->getUrlImage($v["cover"]);
                    }
                }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function info()
    {
        $param = $this->request()->getRequestParam();

        try {
            $vipGoods = LiveTongChengModel::create()
                ->get([
                    'id' => $param['id'],
                    'status' => [LiveTongChengModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $vipGoods, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function add()
    {
        $param = $this->request()->getRequestParam();
        try {
            $data = [
                'name' => trim($param['name']),
                'fileType' => trim($param['fileType']),
                'cover' => trim($param['cover']),
                'viewer' => intval($param['viewer']),
                'url' => trim($param['url']),
                'title' => trim($param['title']),
                'introduce' => trim($param['introduce']),
                'sort' => intval($param['sort']),
                'project' => trim($param['project']),
                'feature' => trim($param['feature']),
                'adId' => intval($param['adId']),
                'distance' => trim($param['distance']),
            ];
            $result = LiveTongChengModel::create($data)->save();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_ADD,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
    public function edit()
    {
        $param = $this->request()->getRequestParam();
        try {
            $data = [
               'name' => trim($param['name']),
                'fileType' => trim($param['fileType']),
                'cover' => trim($param['cover']),
                'viewer' => intval($param['viewer']),
                'url' => trim($param['url']),
                'title' => trim($param['title']),
                'introduce' => trim($param['introduce']),
                'sort' => intval($param['sort']),
                'project' => trim($param['project']),
                'feature' => trim($param['feature']),
                'adId' => intval($param['adId']),
                'distance' => trim($param['distance']),
            ];

            $result = LiveTongChengModel::create()->update($data,["id"=>$param['id']]);

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
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'id' => $param['id'],
                'status' => intval($param['status']),
            ];
            $result = LiveTongChengModel::create()->update($data,["id"=>$param['id']]);
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
    public function getAd(){
        $param = $this->request()->getRequestParam();
        try {
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(AdModel::create()->getTableName() . ' AS ad', 'ad.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>78,"ad.status"=>1])
            ->order("relation.sort","desc")
            ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $res,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
}