<?php

namespace App\HttpController\Api\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\HttpController\Api\User\UserBase;
use App\Model\Video\ShortVideoDyModel;
use App\Model\Video\ShortVideoDyClickRecordModel;
use App\Model\Video\ShortVideoDyCollectRecordModel;
use App\Model\Video\ShortVideoDyUserModel;
use App\Model\Video\ShortVideoDyFocusRecordModel;
use App\Model\Video\ShortVideoDyReplyModel;
use App\Model\User\UserModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\ORM\DbManager;
use App\Model\Common\ConfigNewModel;
use Exception;
use Throwable;

/**
 * Class ShortVideo
 * @package App\HttpController\Api\Video
 * @ApiGroup(groupName="短视频 Video/ShortVideo")
 * @ApiGroupDescription("短视频相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class ShortVideoDy extends UserBase
{

    /**
     * 短视频列表（同搜索，同分类筛选）
     * @Api(name="短视频列表（同搜索，同分类筛选）",path="/Api/Video/ShortVideo/videoList")
     * @ApiDescription("短视频列表（同搜索，同分类筛选）。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="vodName", alias="影片名", type="string", optional="", mbLengthMin="1", description="影片名")
     * @ApiSuccess({"code":200,"result":{"total":20,"list":[{"vodId":1,"vodName":"测试3323","likeCount":32,"vodPic":"https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg","vodPlayUrl":"https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8","sort":1}],"options":[]},"systemTimestamp":1698046584,"systemDateTime":"2023-10-23 15:36:24","msg":"OK"})
     */
   
    public function videoList()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);     
            $model=ShortVideoDyModel::create()->alias('video');
            //推荐or最新
            if(isset($param['sortType']) && $param['sortType']) {
                switch($param['sortType']){
                    case 0:
                        //推荐
                        $model->where(["video.is_recommod"=> 1]);
                        break;
                    default:
                        //最新
                        break;
                }
            }
            $keyword['status'] = ShortVideoDyModel::STATE_NORMAL;
            $field = [
                'video.vodId',
                'video.vodName',
                'video.vodPic',
                'video.vodPlayUrl',
                'video.click',
                'video.collection',
                'video.is_aws',
                "video.reply",
                "video.fake_uid",
                "user.id",
                "user.username",
                "user.img_src",
            ];
            $data =$model
                ->join(ShortVideoDyUserModel::create()->getTableName() . ' AS user', 'user.id = video.fake_uid', 'LEFT')
                ->order('video.sort', 'DESC')
                ->where(["status"=>1])
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);
            if($data["list"]){
                $vodIdArray=[];
                foreach($data["list"] as $k=>$v){
                    $vodIdArray[]=$v->vodId;
                    $data["list"][$k]->isCollect=0;
                    $data["list"][$k]->isClick=0;
                    $data["list"][$k]->isFocus=0;
                }
                //是否已收藏
                $collectRes=ShortVideoDyCollectRecordModel::create()->where(["uid"=>$userId])->where("vod_id",$vodIdArray,"in")->field("vod_id")->all();
                
                foreach($data["list"] as $kcollect=>$vcollect){
                    foreach($collectRes as $kcollects=>$vcollects){
                            if($vcollect->vodId==$vcollects["vod_id"]){
                                $data["list"][$kcollect]->isCollect=1;   
                            }
                    }
                }
                //是否已点击-心过
                $clickRes=ShortVideoDyClickRecordModel::create()->where(["uid"=>$userId])->where("vod_id",$vodIdArray,"in")->field("vod_id")->all();
                
                foreach($data["list"] as $kcl=>$vcl){
                    foreach($clickRes as $kclick=>$vclick){
                       
                            if($vcl->vodId==$vclick["vod_id"]){
                               
                                $data["list"][$kcl]->isClick=1; 
                                 
                            }
                    }
                }
      
                //是否已关注
                $focusRes=ShortVideoDyFocusRecordModel::create()->where(["uid"=>$userId])->all();
                foreach($data["list"] as $kl=>$vl){
                    foreach($focusRes as $kc=>$vc){
                            if($vl->fake_uid==$vc["fask_uid"]){
                                $data["list"][$kl]->isFocus=1;   
                            }
                    }
                }
            }
            // 短视频分页还是按照正常的顺序分页，但是返回的列表打乱一下顺序保证每次都不一样。
            shuffle($data['list']);
            if($data["list"]){
                $awsHost=ConfigNewModel::create()->where("cfgKey","AwsS3Host")->get();
                foreach($data["list"] as $kll=>$dll){
                    if($dll->is_aws==1){
                        $data["list"][$kll]["vodPlayUrl"]=$awsHost["cfgValue"].$dll["vodPlayUrl"];
                    }
                }
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //点心
    public function like()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $videoModel = ShortVideoDyModel::create();
            //点赞数+1
            //点赞数据添加
            $videoData=ShortVideoDyModel::create()->where(["vodId"=>$param["vodId"]])->lockForUpdate()->get();
            if(!$videoData){
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }
            $videoModel->where(["vodId"=>$param["vodId"]])->update(["click"=>$videoData["click"]+1]);
            $videoClickRecordData=[
            "vod_id"=>$param["vodId"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
           ShortVideoDyClickRecordModel::create($videoClickRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //取消点心
    public function likeCancel()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $videoModel = ShortVideoDyModel::create();
            //点赞数-1
            //点赞数据删除
            $videoData=ShortVideoDyModel::create()->where(["vodId"=>$param["vodId"]])->lockForUpdate()->get();
            if(!$videoData){
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }
            $videoModel->where(["vodId"=>$param["vodId"]])->update(["click"=>$videoData["click"]-1]);

            $res=ShortVideoDyClickRecordModel::create()->get(["uid"=>$userId,"vod_id"=>$param["vodId"]]);
            if($res){
                ShortVideoDyClickRecordModel::create()->destroy(["uid"=>$userId,"vod_id"=>$param["vodId"]],true);
            }
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //收藏
    public function collect()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $videoModel = ShortVideoDyModel::create();
            //点赞数+1
            //点赞数据添加
            $videoData=ShortVideoDyModel::create()->where(["vodId"=>$param["vodId"]])->lockForUpdate()->get();
            if(!$videoData){
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }
            $videoModel->where(["vodId"=>$param["vodId"]])->update(["collection"=>$videoData["collection"]+1]);
            $videocoolectRecordData=[
            "vod_id"=>$param["vodId"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
           ShortVideoDyCollectRecordModel::create($videocoolectRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //取消收藏
    public function collectCancel()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $videoModel = ShortVideoDyModel::create();
            //点赞数-1
            //点赞数据删除
            $videoData=ShortVideoDyModel::create()->where(["vodId"=>$param["vodId"]])->lockForUpdate()->get();
            if(!$videoData){
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }
            $videoModel->where(["vodId"=>$param["vodId"]])->update(["collection"=>$videoData["collection"]-1]);

            $res=ShortVideoDyCollectRecordModel::create()->get(["uid"=>$userId,"vod_id"=>$param["vodId"]]);
            if($res){
                ShortVideoDyCollectRecordModel::create()->destroy(["uid"=>$userId,"vod_id"=>$param["vodId"]],true);
            }
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //评论
    public function reply()
    {
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $videoModel = ShortVideoDyModel::create();
            //回复数+1
            //评论数据添加
            $videoData=ShortVideoDyModel::create()->where(["vodId"=>$param["vodId"]])->lockForUpdate()->get();
            if(!$videoData){
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }
            $videoModel->where(["vodId"=>$param["vodId"]])->update(["reply"=>$videoData["reply"]+1]);
            $videoReplyRecordData=[
            "vod_id"=>$param["vodId"],
            "content"=>$param["content"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
            ShortVideoDyReplyModel::create($videoReplyRecordData)->save();
           DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    //展示评论
    public function replyList(){
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);     
            $keyword['status'] = ShortVideoDyReplyModel::STATE_NORMAL;
            $field = [
                'reply.id',
                'reply.uid',
                'reply.click',
                'reply.content',
                'reply.create_at',
                'reply.update_at',
                "reply.vod_id",
                "user.userId",
                "user.nickname",
                "user.avatar",
            ];

            $data["list"]=ShortVideoDyReplyModel::create()->alias('reply')
                ->join(UserModel::create()->getTableName() . ' AS user', 'reply.uid = user.userId', 'LEFT')
                ->where(["reply.vod_id"=>$param["vodId"]])
                ->order('reply.create_at', 'DESC')
                ->all();
         
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //关注
    public function focus(){
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            $RecordData=[
            "fask_uid"=>$param["fask_uid"],
            "uid"=>$userId,
            "create_at"=>time(),
            "update_at"=>time(),
            ];
            ShortVideoDyFocusRecordModel::create($RecordData)->save();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    public function focusCancel(){
        $param = $this->request()->getRequestParam();
        $userId=$this->who['userId'];
        try {
            ShortVideoDyFocusRecordModel::create()->destroy(["uid"=>$userId,"fask_uid"=>$param["fask_uid"]],true);;
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
}