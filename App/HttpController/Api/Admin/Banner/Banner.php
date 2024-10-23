<?php

namespace App\HttpController\Api\Admin\Banner;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveModel;
use App\Model\Banner\BannerModel;
use EasySwoole\Http\Message\Status;
use App\Model\Navigation\AdGroupRelationModel;
use App\Model\Navigation\AdModel;
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

class Banner extends AdminBase
{
   
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
            $data = BannerModel::create()
                ->where(["is_del"=>BannerModel::STATE_No])
                ->order("sort"," desc")
                ->getAll($page, $keyword, $pageSize, $field);
            if($data["list"]){
                 foreach($data["list"] as $k=>$v){
                    $data["list"][$k]->create_at=date("Y-m-d H:i:s",$v->create_at);
                    $imgData=new uploadNew();
                    $data["list"][$k]->img_src=$imgData->getUrlImage($v["img_src"]);
                 }

            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function add()
    {
        $param = $this->request()->getRequestParam();
        $adRes=BannerModel::create()->where(["adId"=>$param['adId'],"status"=>1])->get();
        if($adRes){
            throw new Exception('操作失败，广告已被其他绑定', Status::CODE_BAD_REQUEST);
        }
        try {
            $data = [
                'img_src' => trim($param['img_src']),
                'url' => trim($param['url']),
                'is_internal' => intval($param['is_internal']),
                'name' => trim($param['name']),
                'sort' => trim($param['sort']),
                'status' => trim($param['status']),
                'create_at' => time(),
                'update_at' => time(),
            ];

            $result = BannerModel::create($data)->save();

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
    public function info()
    {
        $param = $this->request()->getRequestParam();

        try {
            $res = BannerModel::create()
                ->get([
                    'id' => $param['id'],
                ]);
                $imgData=new uploadNew();
                $res["fileType"]="up";
                $res["img_show"]=$imgData->getUrlImage($res["img_src"]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function edit()
    {
        $param = $this->request()->getRequestParam();
        $adRes=BannerModel::create()->where(["adId"=>$param['adId'],"status"=>1])->get();
            if($adRes["id"]!=$param['id']){
                throw new Exception('操作失败，广告已被其他绑定', Status::CODE_BAD_REQUEST);
            }
        try {
            $data = [
                'img_src' => trim($param['img_src']),
                'url' => trim($param['url']),
                'is_internal' => trim($param['is_internal']),
                'name' => trim($param['name']),
                'sort' => intval($param['sort']),
                'status' => trim($param['status']),
                'update_at' =>time(),
            ];

            $result = BannerModel::create()->update($data,["id"=>$param["id"]]);

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
                'status' => intval($param['status']),
                "update_at"=>time(),
            ];

            $result = BannerModel::create()->update($data,["id"=>$param["id"]]);

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
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'is_del' => BannerModel::DELETED,
            ];

            $result = BannerModel::create()->update($data,["id"=>$param["id"]]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_DELETE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
    public function getAd(){
        $param = $this->request()->getRequestParam();
        try {
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(AdModel::create()->getTableName() . ' AS ad', 'ad.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>81,"ad.status"=>1])
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