<?php

namespace App\HttpController\Api\Post;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\Post\PostModel;
use App\Model\Post\PostReplyModel;
use App\Model\Post\PostClickRecordModel;
use App\Model\Post\PostFocusRecordModel;

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
            $userId=$this->who['userId'];
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'post.id',
                'post.title',
                'post.type',
                'post.reply',
                'post.click',
                'post.img_src',
                'post.vodio_src',
                'post.focus',
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
           if($result["list"]){
                    $postIdArray=[];
                    foreach($result["list"] as $k=>$v){
                        $postIdArray[]=$v->id;
                        $result["list"][$k]->isClick=0;
                        $result["list"][$k]->isFouce=0;
                    }
                     //是否关注、点赞
                    $ClickRes=PostClickRecordModel::create()->where(["uid"=>$userId])->where("post_id",$postIdArray,"in")->all(); 
                    foreach($result["list"] as $kl=>$vl){
                        foreach($ClickRes as $kc=>$vc){
                                if($vl->id==$vc["post_id"]){
                                    $result["list"][$kl]->isClick=1;   
                                }
                        }
                    }
                    $fouceRes=PostFocusRecordModel::create()->where(["uid"=>$userId])->where("post_id",$postIdArray,"in")->all();  
                   
                    foreach($result["list"] as $kl=>$vl){
                        foreach($fouceRes as $kf=>$vf){
                                if($vl->id==$vf->post_id){

                                    $result["list"][$kl]->isFouce=1;   
                                }
                        }
                    }
            }
                  
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
                "click"=>PostClickRecordModel::create()->where(["uid"=>$userId])->count(1),
                "release"=>$model->where(["uid"=>$userId]) ->where(["is_del"=>PostModel::NO_DELETE])->count(1),
                "fouce"=>PostFocusRecordModel::create()->where(["uid"=>$userId])->count(1)
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
            "post_id"=>$param["postId"],
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
            $field = ['click'];
            //判断帖子、回复 
            switch($param["type"]){
                case 1:
                    // 点赞帖子
                    $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->field($field)->lockForUpdate()->get();
                    $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["click"=>$postData["click"]+1]);
                    break;
                case 2:
                    //点赞回复
                    $postReplyData=PostReplyModel::create()->where(["id"=>$param["postId"]])->field($field)->lockForUpdate()->get();
                    PostReplyModel::create()->where(["id"=>$param["postId"]])->update(["click"=>$postReplyData["click"]+1]);
                    break;
                default:
                throw new Exception('错误的type', Status::CODE_BAD_REQUEST);
            }
            $replyRecordData=[
            "type"=>$param["type"],
            "post_id"=>$param["postId"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
           PostClickRecordModel::create($replyRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //取消点赞/回复
    public function clickCancel(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            //判断帖子、回复 
            switch($param["type"]){
                case 1:
                    // 点赞帖子
                    $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->lockForUpdate()->get();
                    $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["click"=>$postData["click"]-1]);
                    break;
                case 2:
                    //点赞回复
                    $postReplyData=PostReplyModel::create()->where(["id"=>$param["postId"]])->lockForUpdate()->get();
                    PostReplyModel::create()->where(["id"=>$param["postId"]])->update(["click"=>$postReplyData["click"]-1]);
                    break;
                default:
                throw new Exception('错误的type', Status::CODE_BAD_REQUEST);
            }
            PostClickRecordModel::create()->destroy(["post_id"=>$param["postId"],"uid"=>$userId,"type"=>$param["type"]],true);
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //关注
    public function focus(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            //判断帖子、回复 
            $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->lockForUpdate()->get();
            $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["focus"=>$postData["focus"]+1]);
            $focusRecordData=[
            "post_id"=>$param["postId"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            "post_uid"=>$postData->uid,
            ];
           PostFocusRecordModel::create($focusRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
     //取消关注
     public function focusCancel(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->lockForUpdate()->get();
           
            $postModel->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->update(["focus"=>$postData["focus"]-1]);
                    
           PostFocusRecordModel::create()->destroy(["post_id"=>$param["postId"],"uid"=>$userId],true);
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //删除帖子
    public function delete(){
        $param = $this->request()->getRequestParam();
        try {
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $postModel = PostModel::create();
            //帖子回复数+1
            //回帖数据添加
            $postData=PostModel::create()->where(["is_del"=>PostModel::NO_DELETE,"id"=>$param["postId"]])->get();
            if($postData->uid!=$userId){
                throw new Exception('删除失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
                    
           PostModel::create()->update(["id"=>$param["postId"],"is_del"=>PostModel::DELETE]);
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
}