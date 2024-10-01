<?php
namespace App\HttpController\Api\Post;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\FeedBack\FeedBackModel;
use App\Model\Post\PostReplyModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use App\Service\Oss\LocalOssService;
use Exception;
use Throwable;


class FeedBack extends UserBase
{
    //意见列表
    public function list()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'type',
                'content',
                'create_at',
            ];
            $model = FeedBackModel::create();
            $result= $model
                ->where(["uid"=>$userId])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    //提交意见
    public function add(){
        $param = $this->request()->getRequestParam();
        try {

            $userId=$this->who['userId'];
            $data = [
                'content'=>$param["content"],
                'type'=>$param["type"],
                'create_at'=>time(),
                'update_at'=>time(),
                "contact"=>$param["contact"],
                'uid'=>$userId,
            ];
            $result= FeedBackModel::create($data)->save();
  
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
   
}