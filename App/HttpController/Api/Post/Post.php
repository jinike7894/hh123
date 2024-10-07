<?php

namespace App\HttpController\Api\Post;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\Post\PostModel;
use App\Model\Post\PostReplyModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;

use Exception;
use Throwable;


class Post extends UserBase
{
    //帖子列表
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'post.id',
                'post.title',
                'post.type',
                'post.click',
                'post.reply',
                'post.uid',
                'user.userId',
                'user.avatar',
                'user.nickname',
            ];
            $model = PostModel::create()
            ->alias('post')
            ->join(UserModel::create()->getTableName() . ' AS user', 'post.uid = user.userId', 'LEFT');
            //判断帖子类型
            switch ($param["type"]) {
                case 1:
                    // 推荐
                    $model->where(["post.is_recommend"=>1])->order("post.create_at","desc");
                    break;
                case 2:
                    // 最新
                    $model->order("post.create_at","desc");
                    break;
                case 3||4:
                    // 同城||茶馆
                    $model->where(["post.type"=>$param["type"]])->order("post.create_at","desc");
                    break;
                default:
                throw new Exception('错误的type', Status::CODE_BAD_REQUEST);
            }
            $result= $model
                ->where(["post.is_del"=>PostModel::NO_DELETE])
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    //帖子个人中心展示
    public function personList()
    {
        $param = $this->request()->getRequestParam();
        try {
            //查询用户数据、点赞、发布
            $userId=$this->who['userId'];
            $model = PostModel::create();
            $result["userData"]= UserModel::create()->get($this->who['userId']);
            $result["PostInfoData"]= [
                "click"=>PostReplyModel::create()->where(["uid"=>$userId])->count(),
                "release"=>$model->where(["uid"=>$userId]) ->where(["is_del"=>PostModel::NO_DELETE])->count()
            ];
            //查询帖子
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'title',
                'type',
                'click',
                'reply',
                'uid',
            ];
            
            $result["postData"]= $model
                ->where(["is_del"=>PostModel::NO_DELETE])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    //帖子详情
    public function info(){
        $param = $this->request()->getRequestParam();
        try {
            $postModel = PostModel::create();
            $postRplyModel = PostReplyModel::create();
            //查询帖子数据
            $field = [
                'id',
                'title',
                'type',
                'click',
                'reply',
                'uid',
            ];
            $result["postData"]=$postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->field($field)->get();
            //查询帖子回复
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $replyField = [
                'id',
                'content',
                'uid',
                'click',
            ];
            $result["replyData"]= $postRplyModel
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $replyField);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    //发布帖子
    public function release(){
        $param = $this->request()->getRequestParam();
        try {

            $userId=$this->who['userId'];
            $data = [
                'title'=>$param["title"],
                'type'=>$param["type"],
                'create_at'=>time(),
                'update_at'=>time(),
                'vodio_src'=>$param["vodio_src"],
                'img_src'=>$param["img_src"],
                'uid'=>$userId,
            ];
            $result= PostModel::create($data)->save();
  
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    //回复帖子
    public function replay(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            $field = [
                'id',
                'type',
                'click',
                'reply',
                'uid',
            ];
           $postData=$postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->field($field)->lockForUpdate()->get();
           $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["reply"=>$postData["reply"]+1]);
           $replyData=[
            "content"=>$param["content"],
            "post_id"=>$param["post_id"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
           ];
           PostReplyModel::create($replyData)->save();
            DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //点赞帖子/回复
    public function click(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            $field = ['click',];
            //判断帖子、回复 
            switch($param["type"]){
                case 1:
                    // 点赞帖子
                    $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->field($field)->lockForUpdate()->get();
                    $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["reply"=>$postData["reply"]+1]);
                    break;
                case 2:
                    //点赞回复
                    $postReplyData=PostReplyModel::create()->where(["id"=>$param["postId"]])->field($field)->lockForUpdate()->get();
                    PostReplyModel::create()->where(["id"=>$param["postId"]])->update(["reply"=>$postReplyData["reply"]+1]);
                    break;
                default:
                throw new Exception('错误的type', Status::CODE_BAD_REQUEST);
            }
            $replyRecordData=[
            "type"=>$param["type"],
            "post_id"=>$param["post_id"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
           PostReplyModel::create($replyRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
  
}