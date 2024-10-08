<?php

namespace App\HttpController\Api\Notice;

use App\Enum\ConfigKey\SystemConfigKey;

use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\Notice\NoticeModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use App\Service\Oss\LocalOssService;
use Exception;
use Throwable;


class Notice extends UserBase
{
    //获取公告、信息
    public function list()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id ',
                'type',
                'title',
                'content',
                'create_at',
            ];
            $model = NoticeModel::create();
            $result= $model
                ->where("(property=1 or uid="+$userId+")")//全员/私信
                ->where(["is_del"=>NoticeModel::NODELETE])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

}