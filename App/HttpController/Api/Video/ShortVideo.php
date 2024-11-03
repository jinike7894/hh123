<?php

namespace App\HttpController\Api\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\HttpController\Api\User\UserBase;
use App\Model\Video\ShortVideoModel;
use App\RedisKey\Video\ShortVideoKey;
use App\Service\Video\ShortVideoService;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\RedisPool\RedisPool;
use App\Model\Video\ShortVideoTagModel;
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
class ShortVideo extends UserBase
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

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            
            // 这里是搜索
            if (isset($param['vodName']) && $param['vodName']) {
                $keyword['vodName'] = $param['vodName'];

                ShortVideoService::getInstance()->setHotWords($param['vodName']);
            }
            $model=ShortVideoModel::create();
            //tag
            if(isset($param['shortTag']) && $param['shortTag']) {
                
                $model->where(["shortTag"=> $param['shortTag']]);
            }
            //推荐or最新
            if(isset($param['sortType']) && $param['sortType']) {
                switch($param['sortType']){
                    case 0:
                        //推荐
                        $model->where(["is_recommod"=> 1]);
                        break;
                    default:
                        //最新
                        break;
                }
               
            }
            $keyword['status'] = ShortVideoModel::STATE_NORMAL;
            $field = [
                'vodId',
                'vodName',
                'vodPic',
                'vodPlayUrl',
                'fileType',
                'likeCount',
                'is_aws',
            ];
            $data =$model
                ->order('sort', 'DESC',"id","desc")
                ->getAll($page, $keyword, $pageSize, $field);
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

    /**
     * 短视频详情
     * @Api(name="短视频详情",path="/Api/Video/ShortVideo/videoDetail")
     * @ApiDescription("短视频详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="影片id")
     * @ApiSuccess({"code":200,"result":{"vodId":1,"vodName":"测试3323","likeCount":32,"vodPic":"https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg","vodPlayUrl":"https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8"},"systemTimestamp":1698046443,"systemDateTime":"2023-10-23 15:34:03","msg":"OK"})
     */
    public function videoDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = ShortVideoModel::create()
                ->field([
                    'vodId',
                    'vodName',
                    'vodPic',
                    'vodPlayUrl',
                    'fileType',
                    'likeCount',
                ])
                ->where(['status' => ShortVideoModel::STATE_NORMAL])
                ->get($param['vodId']);

            if (!$data) {
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 短视频点赞
     * @Api(name="短视频点赞",path="/Api/Video/ShortVideo/like")
     * @ApiDescription("短视频点赞，如果已经点赞过，再次请求则为取消点赞。点赞结果为1，取消点赞结果为0")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="影片id")
     * @ApiSuccess({"code":200,"result":1,"systemTimestamp":1698212049,"systemDateTime":"2023-10-25 13:34:09","msg":"OK"})
     * @ApiSuccess({"code":200,"result":0,"systemTimestamp":1698212049,"systemDateTime":"2023-10-25 13:34:09","msg":"OK"})
     */
    public function like()
    {
        $param = $this->request()->getRequestParam();

        try {
            $shortVideo = ShortVideoModel::create()
                ->get([
                    'vodId' => $param['vodId'],
                    'status' => ShortVideoModel::STATE_NORMAL,
                ]);

            if (!$shortVideo) {
                throw new Exception('无效的短视频id', Status::CODE_BAD_REQUEST);
            }

            $isAlreadyLike = ShortVideoService::getInstance()->isAlreadyLike($this->who['userId'], $shortVideo->vodId);
            if ($isAlreadyLike) {
                ShortVideoService::getInstance()->dislike($this->who['userId'], $shortVideo->vodId);
                $result = 0;
            } else {
                ShortVideoService::getInstance()->like($this->who['userId'], $shortVideo->vodId);
                $result = 1;
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 搜索热词列表
     * @Api(name="搜索热词列表",path="/Api/Video/ShortVideo/searchKeyWords")
     * @ApiDescription("搜索热词列表。")
     * @Method(allow=["GET"])
     * @ApiSuccess({"code":200,"result":["dd","aaaa"],"systemTimestamp":1698069482,"systemDateTime":"2023-10-23 21:58:02","msg":"OK"})
     */
    public function searchKeyWords()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = ShortVideoService::getInstance()->getHotWords();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取猎奇短视频tag
    public function tag(){
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'name',
                'img_src',
                'sort',
                'create_at',
            ];
            $data = ShortVideoTagModel::create()
                ->where(["is_del"=>ShortVideoTagModel::NODELETE])
                ->order("sort","desc")
                ->getAll($page, $keyword, $pageSize, $field);
                if($data["list"]){
                    foreach($data["list"] as $k=>$v){
                    $data["list"][$k]->num=$k%6;
                    }
                }
           
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}