<?php

namespace App\HttpController\Api\Admin\Notice;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveModel;
use App\Model\Notice\NoticeModel;
use EasySwoole\Http\Message\Status;

use Exception;
use Throwable;

class Notice extends AdminBase
{
   //后台获取公告管理
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'type',
                'title',
                'content',
                'create_at',
            ];
            $data = NoticeModel::create()
                ->where(["is_del"=>NoticeModel::NODELETE])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
                foreach($data["list"] as $k=>$v){
                    $data["list"][$k]->create_at=date("Y-m-d H:i:s",$v["create_at"]);
                }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //后台添加公告、信息
    public function add()
    {
        $param = $this->request()->getRequestParam();
        if(isset($param["uid"])&&$param["uid"]!=""){
            $param["property"]=2;  //个人
        }else{
            $param["property"]=1;  //全员
        }
        try {
            $data = [
                'type' => trim($param['type']),
                'title' => trim($param['title']),
                'content' => trim($param['content']),
                'uid' => intval($param['uid']),
                'property' => trim($param['property']),
                'create_at' => time(),
                'update_at' => time(),
            ];

            $result = NoticeModel::create($data)->save();

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
            $res = NoticeModel::create()
                ->get([
                    'id' => $param['id'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function edit()
    {
        $param = $this->request()->getRequestParam();
        if(isset($param["uid"])&&$param["uid"]!=""){
            $param["property"]=2;  //个人
        }else{
            $param["property"]=1;  //全员
        }
        try {
            $data = [
                'type' => intval($param['type']),
                'title' => trim($param['title']),
                'content' => trim($param['content']),
                'property' => trim($param['property']),
                'uid' => intval($param['uid']),
                'update_at' =>time(),
            ];

            $result = NoticeModel::create()->update($data,["id"=>$param["id"]]);

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
                'is_del' => NoticeModel::DELETED,
                "update_at"=>time(),
            ];

            $result = NoticeModel::create()->update($data,["id"=>$param["id"]]);

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
}