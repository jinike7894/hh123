<?php

namespace App\HttpController\Api\Notice;

use App\Enum\ConfigKey\SystemConfigKey;

use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\Notice\NoticeModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use App\Service\Oss\LocalOssService;
use EasySwoole\Mysqli\QueryBuilder;
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
            $page = (int)($param['page'] ?? 1)-1;
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id ',
                'type',
                'title',
                'content',
                'create_at',
            ];
            $model = NoticeModel::create();
            $queryBuild = new QueryBuilder();
            $sql="select id,type,title,content,create_at from notice where (property=? or uid=".$userId.") and is_del  = 0  order by create_at desc LIMIT ".$page.", ".$pageSize;
            $queryBuild->raw($sql,[1]);
            $data = DbManager::getInstance()->query($queryBuild);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

}