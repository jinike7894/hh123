<?php

namespace App\HttpController\Api\User;

use App\Enum\ConfigKey\AppConfigKey;
use App\Enum\ConfigKey\OssConfigKey;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Model\Common\ConfigModel;
use App\Enum\Upload;
use App\Model\User\UserAiFaceRecordModel;
use App\Model\User\UserAiPicTempModel;
use App\Model\User\UserAiRecordModel;
use App\Model\User\UserAiStripRecordModel;
use App\Model\User\UserAiVideoTempModel;
use App\Model\User\UserInviteModel;
use App\Model\User\UserModel;
use App\Service\Message\JSMSService;
use App\Service\Oss\LocalOssService;
use App\Service\User\UserService;
use App\Utility\Func;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;

use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * Class User
 * @package App\HttpController\Api\User
 * @ApiGroup(groupName="用户 User/User")
 * @ApiGroupDescription("用户相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class User extends UserBase
{

    /**
     * 获取身份卡
     * @Api(name="获取身份卡",path="/Api/User/User/getIdentityCard")
     * @ApiDescription("获取身份卡")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess({"code":200,"result":{"identityCard":"+UuHM/pM7ocfYQsSIvdlfw==","userId":100001,"nickname":"游客6538CDA650221"},"systemTimestamp":1701780171,"systemDateTime":"2023-12-05 20:42:51","msg":"OK"})
     */
    public function getIdentityCard()
    {
        $param = $this->request()->getRequestParam();

        try {
            $user = UserModel::create()->get($this->who['userId']);
            $data['identityCard'] = UserService::getInstance()->getIdentityCard($user);
            $data['userId'] = $user->userId;
            $data['nickname'] = $user->nickname;
            $data['userName'] = $user->userName;
            $data['avatar'] = $user->avatar;
            $data['gender'] = $user->gender;
            $data['profile'] = $user->profile;
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //更新用户信息
    public function updateUserInfo(){
        $param = $this->request()->getRequestParam();

        try {
            $user = UserModel::create()->get($this->who['userId']);
            if(!$user){
                throw new Exception('用户错误。', Status::CODE_BAD_REQUEST);
            }
            $data=[];
            if(isset($param["userName"])&&$param["userName"]!=""){
                $data['userName'] = $param["userName"];
            }
            if(isset($param["nickname"])&&$param["nickname"]!=""){
                $data['nickname'] = $param["nickname"];
            }
            if(isset($param["avatar"])&&$param["avatar"]!=""){
                $data['avatar'] = $param["avatar"];
            }
            if(isset($param["gender"])&&$param["gender"]!=""){
                $data['gender'] = $param["gender"];
            }
            if(isset($param["profile"])){
                $data['profile'] = $param["profile"];
            }
            UserModel::create()->update($data,["userId "=>$this->who['userId']]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //修改密码
    public function updatePass(){
        $param = $this->request()->getRequestParam();
        
        try {
            if(!isset($param["password"])&&$param["password"]==""){
                throw new Exception('参数错误', Status::CODE_BAD_REQUEST);
            }
            $user = UserModel::create()->get($this->who['userId']);
            if(!$user){
                throw new Exception('用户错误。', Status::CODE_BAD_REQUEST);
            }
            $data=[];
            $data['password'] = md5($param["password"]);
            UserModel::create()->update($data,["userId "=>$this->who['userId']]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //邀请码
    public function inviteCode(){
        $param = $this->request()->getRequestParam();
        try {
            if(!isset($param["inviteCode"])||$param["inviteCode"]!=""){
                throw new Exception('邀请码异常', Status::CODE_BAD_REQUEST);
            }
            $userId=$this->who['userId'];
            DbManager::getInstance()->startTransactionWithCount();
            $UserInviteModel = UserInviteModel::create();
            $UserModel = UserModel::create();
            $userData=$UserModel->get(["userId"=>$userId]);
            if(!$userData){
                throw new Exception('邀请码异常!', Status::CODE_BAD_REQUEST);
            }
            //判断是否推广码是否首次
            $inviteData=$UserInviteModel->get(["inviterId"=>$param["inviteCode"]]);
            if(!$inviteData){
                 //新用户首次增加7天
                 UserService::getInstance()->increaseVIPDays($userData, 7);
            }else{
                //新用户首次增加3天
                UserService::getInstance()->increaseVIPDays($userData, 3);
            }
            //添加邀请记录
            $userInviteId = UserInviteModel::create([
                'inviterId' => $userData->userId,
                'inviteeId' => $userId,
                'createDate' => date('Y-m-d'),
            ])->save();
            DbManager::getInstance()->commitWithCount();
        } catch (Throwable $e) {
            DbManager::getInstance()->rollbackWithCount();
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }
    /**
     * 绑定手机号
     * @Api(name="绑定手机号",path="/Api/User/User/bindCellPhone")
     * @ApiDescription("绑定手机号")
     * @Method(allow=["POST"])
     * @Param(name="phoneNumber", alias="手机号码", type="string", required="", mbLength="11", description="手机号码")
     * @Param(name="messageId", alias="消息id", type="string", required="", mbLengthMin="1", description="消息id")
     * @Param(name="code", alias="验证码", type="string", required="", mbLengthMin="1", description="验证码")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1701260096,"systemDateTime":"2023-11-29 20:14:56","msg":"OK"})
     */
    public function bindCellPhone()
    {
        $param = $this->request()->getRequestParam();

        try {

            $phoneNumber = trim($param['phoneNumber']);
            $messageId = trim($param['messageId']);
            $code = trim($param['code']);

            $checkResult = JSMSService::getInstance()->checkCode($phoneNumber, $messageId, $code);

            if (!$checkResult) {
                throw new Exception('验证码不正确，请稍后重试。', Status::CODE_BAD_REQUEST);
            }

            // 只要中国，所以这里写死了+86。
            $result = UserService::getInstance()->bindCellPhone($this->who['userId'], $phoneNumber, '+86');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 获取(刷新)用户信息
     * @Api(name="获取(刷新)用户信息",path="/Api/User/User/getUserInfo")
     * @ApiDescription("获取(刷新)用户信息")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess({"code":200,"result":{"userId":100001,"userGroupId":3,"userGroupExpiryDate":"2024-04-02","nickname":"游客6538CDA650221","balance":"0.00","phoneState":1,"identityCard":"+UuHM/pM7ocfYQsSIvdlfw=="},"systemTimestamp":1701935918,"systemDateTime":"2023-12-07 15:58:38","msg":"OK"})
     */
    public function getUserInfo()
    {
        $param = $this->request()->getRequestParam();

        try {
            $user = UserModel::create()->get($this->who['userId']);

            $data = $this->getUserLoginData($user);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 我的邀请列表
     * @Api(name="我的邀请列表",path="/Api/User/User/myInvitationList")
     * @ApiDescription("我的邀请列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function myInvitationList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['ui.inviterId'] = $this->who['userId'];

            $field = [
                'ui.userInviteId',
                'ui.inviterId',
                'ui.inviteeId',
                'ui.createTime',
                'u.nickname AS inviteeNickname',
            ];

            $data = UserInviteModel::create()
                ->alias('ui')
                ->join(UserModel::create()->getTableName() . ' AS u', 'u.userId = ui.inviteeId', 'LEFT')
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai换脸-新
     * @Api(name="ai换脸-新",path="/Api/User/User/submitAiFace")
     * @ApiDescription("ai换脸-新")
     * @Method(allow=["POST"])
     * @Param(name="type", alias="1 换脸图片 2 换脸视频", type="string", required="",   description="1 换脸图片 2 换脸视频")
     * @Param(name="file", alias="上传想换脸的图片", type="string",  description="上传想换脸的图片")
     * @Param(name="imgCode", alias="图片模版，type为2时不需要传", type="string",   description="图片模版，type为2时不需要传")
     * @Param(name="templatePic", alias="图片文件，type为2时不需要传", type="string",   description="图片文件，type为2时不需要传")
     * @Param(name="videoCode", alias="视频模版，type为1时不需要传", type="string",   description="视频模版，type为1时不需要传")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1701260096,"systemDateTime":"2023-11-29 20:14:56","msg":"OK"})
     */
    public function submitAiFace()
    {
        $param = $this->request()->getRequestParam();
        try {
            $type = trim($param['type']);
            $videoCode = trim($param['videoCode']);
            $imgCode = trim($param['imgCode']);

            /* 检查额度开始 */
            $userId = $this->who['userId'];
            $user = UserModel::create()->where(['userId' => $userId])->get();
            if($type == 1 && $user['AiFaceImg'] <= 0){
                throw new Exception('换脸图片额度不足，请充值', Status::CODE_BAD_REQUEST);
            }
            if($type == 2 && $user['AiFaceVideo'] <= 0){
                throw new Exception('换脸视频额度不足，请充值', Status::CODE_BAD_REQUEST);
            }
            /* 检查额度结束 */

            if($type ==1){
                $url = 'http://api.nudeai.vip/nude/ai/out/submitAiFaceImg';
            }
            if($type ==2){
                $url = 'http://api.nudeai.vip/nude/ai/out/submitAiFaceVideo';
                if(!isset($param['videoCode'])){
                    throw new Exception('videoCode 视频模版必须填写', Status::CODE_BAD_REQUEST);
                }
            }

            $userCode = '';
            for ($i = 0; $i < 20; $i++) {
                $userCode .= mt_rand(0, 9);
            }
            $userCode = $userCode.$this->who['userId'];

            /* 处理上传图片 */
            $picPath = LocalOssService::getInstance()->uploadImage($this->request(), 'facePic');
            $pic = $picPath['path'];
            $picFullTempFileName = Func::getPublicPath() . $pic;
            /* 处理上传图片end */

          if($type == 1){
              if(isset($param['imgCode'])){
                  /* 处理base64图片 */
                  $userAiPic = UserAiPicTempModel::create()->get(['imgCode' => $imgCode]);
//                  $imgUrl = $userAiPic['imgUrl'];
//                  $path = Upload::getImageDatePath($type);
//                  $dirPath = Func::getPublicPath() . DIRECTORY_SEPARATOR . $path;
//                  $appFileName = Func::CreateGuid() . '.' . 'png';
//                  File::createDirectory($dirPath);
//                  $imageSrc= $dirPath."/". $appFileName;
//                  file_put_contents($imageSrc, base64_decode($imgUrl));

                  $imageSrc = Func::getPublicPath() . $userAiPic['imgPath2'];

                  /* 处理base64图片end */
              }else{
                  /* 处理上传图片 */
                  $picPath = LocalOssService::getInstance()->uploadImage($this->request(), 'templatePic', 'templatePic');
                  $pic = $picPath['path'];
                  $imageSrc = Func::getPublicPath() . $pic;
                  /* 处理上传图片end */
              }


              $postData = array(
                  'templatePic' => new \CURLFile($imageSrc, Func::getMimetype($imageSrc), basename($imageSrc)),
                  'facePic' =>  new \CURLFile($picFullTempFileName, Func::getMimetype($picFullTempFileName), basename($pic)),
                  'merchantAcct' => 'mitao',
                  'userCode' => $userCode,
              );
          }else{
              /* 处理脱衣视频 */

//            facePic 人脸图片
//            merchantAcct 商户号
//            userCode 商户记录唯一code
//            videoCode 模板code
              $postData = array(
                  'videoCode' => $videoCode,
                  'facePic' =>  new \CURLFile($picFullTempFileName, Func::getMimetype($picFullTempFileName), basename($pic)),
                  'merchantAcct' => 'mitao',
                  'userCode' => $userCode,
              );
          }

            $tmpInfo = Func::curlPost($url,$postData);
            $tmpInfoArr = json_decode($tmpInfo,true);

            if(!isset($tmpInfoArr['data'])){
                throw new Exception(json_encode($tmpInfoArr), Status::CODE_BAD_REQUEST);
            }
            $recordCode = $tmpInfoArr['data'];
            $data = [
                'recordCode' => $recordCode,
                'userCode' => $userCode,
                'userId' => $this->who['userId'],
                'aiType' => $type,
                'createTime' => date('Y-m-d H:i:s'),
                'updateTime' => date('Y-m-d H:i:s'),
            ];
            UserAiFaceRecordModel::create($data)->save();

            /* 减少额度开始 */
            $typeMap = [
                1 => 'AiFaceImg',
                2 => 'AiFaceVideo',
            ];
            $aiType = $typeMap[$type];
            UserService::getInstance()->checkAiTimes($this->who['userId'], $aiType);
            /* 减少额度结束 */

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $tmpInfoArr, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * ai脱衣-新
     * @Api(name="ai脱衣-新",path="/Api/User/User/submitAiStrip")
     * @ApiDescription("ai脱衣-新")
     * @Method(allow=["POST"])
     * @Param(name="file", alias="上传想换脸的图片", type="string",  description="上传想换脸的图片")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1701260096,"systemDateTime":"2023-11-29 20:14:56","msg":"OK"})
     */
    public function submitAiStrip()
    {
        $param = $this->request()->getRequestParam();
        try {

            $userCode = '';
            for ($i = 0; $i < 20; $i++) {
                $userCode .= mt_rand(0, 9);
            }

            /* 检查额度开始 */
            $userId = $this->who['userId'];
            $user = UserModel::create()->where(['userId' => $userId])->get();
            if($user['AiPicture'] <= 0){
                throw new Exception('脱衣额度不足，请充值', Status::CODE_BAD_REQUEST);
            }
            /* 检查额度结束 */


            /* 处理上传图片 */
            $picPath = LocalOssService::getInstance()->uploadImage($this->request(), 'facePic');
            $pic = $picPath['path'];
            $picFullTempFileName = Func::getPublicPath() . $pic;
            /* 处理上传图片end */

            $postData = array(
                'file' =>  new \CURLFile($picFullTempFileName, Func::getMimetype($picFullTempFileName), basename($pic)),
                'merchantAcct' => 'mitao',
                'merchantPicId' => $userCode,
            );

            $url = "https://api.nudeai.vip/nude/ai/out/submitAiPictureNew";
            $tmpInfo = Func::curlPost($url,$postData);
            $tmpInfoArr = json_decode($tmpInfo,true);

            if(!isset($tmpInfoArr['data'])){
                throw new Exception(json_encode($tmpInfoArr), Status::CODE_BAD_REQUEST);
            }
            $recordCode = $tmpInfoArr['data'];
            $data = [
                'recordCode' => $recordCode,
                'merchantPicId' => $userCode,
                'userId' => $this->who['userId'],
                'createTime' => date('Y-m-d H:i:s'),
                'updateTime' => date('Y-m-d H:i:s'),
            ];
            UserAiStripRecordModel::create($data)->save();

            /* 减少额度开始 */
            UserService::getInstance()->checkAiTimes($this->who['userId'], 'AiPicture');
            /* 减少额度结束 */

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $tmpInfoArr, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai换脸记录列表-新
     * @Api(name="ai换脸记录列表-新",path="/Api/User/User/aiFaceRecordList")
     * @ApiDescription("ai换脸记录列表-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="type", alias="1图片2视频", type="string",  required="",  description="1图片2视频")
     * @Param(name="status", alias="0 排队中 1 处理中 2 成功 3 失败 4 异常 5 违规 6 队列中", type="string", required="",   description="1图片2视频3脱衣")
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function aiFaceRecordList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $type = $param['type'];
            $status = $param['status'];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['userId'] = $this->who['userId'];
            $keyword['aiType'] = $type;
            $keyword['aiStatus'] = $status;

            $field = [
                'recordCode',
                'userCode',
                'userId',
                'aiStatus',
                'aiType',
                'generateContent'
            ];

            $data = UserAiFaceRecordModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai脱衣记录列表-新
     * @Api(name="ai脱衣记录列表-新",path="/Api/User/User/aiStripRecordList")
     * @ApiDescription("ai脱衣记录列表-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="status", alias="0 排队中 1 处理中 2 成功 3 失败 4 异常 5 违规 6 队列中", type="string", required="",  description="1图片2视频3脱衣")
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function aiStripRecordList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $status = $param['status'];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['userId'] = $this->who['userId'];
            $keyword['aiStatus'] = $status;

            $field = [
                'recordCode',
                'merchantPicId',
                'aiStatus',
                'userId',
                'resultList',
            ];

            $data = UserAiStripRecordModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai换脸视频模版列表-新
     * @Api(name="ai换脸视频模版列表-新",path="/Api/User/User/videoTempPageList")
     * @ApiDescription("ai换脸视频模版列表-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="typeId", alias="分类id", type="int", required="",  description="分类Id")
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function videoTempPageList()
    {
        $param = $this->request()->getRequestParam();
        try {
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $keyword['typeId'] = $param['typeId'];
            $field = [
                'videoCode',
//                'videoImg',
                'imgPath',
                'imgPath2',
                'videoName',
                'previewUrl',
                'videoUrl',
                'imgWidth',
                'imgHeight',
                'typeId',
            ];

            $data = UserAiVideoTempModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai换脸图片模版列表-新
     * @Api(name="ai换脸图片模版列表-新",path="/Api/User/User/picTempPageList")
     * @ApiDescription("ai换脸图片模版列表-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function picTempPageList()
    {
        $param = $this->request()->getRequestParam();
        try {
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'imgCode',
//                'imgUrl',
                'imgPath',
                'imgPath2',
                'imgName',
                'imgWidth',
                'imgHeight',
            ];

            $data = UserAiPicTempModel::create()
                ->setDefaultOrder()
                ->getAll($page, [], $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai模版分类
     * @Api(name="ai模版分类",path="/Api/User/User/aiTempTypeList")
     * @ApiDescription("ai模版分类")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"userInviteId":1,"inviterId":100001,"inviteeId":100003,"createTime":"2023-12-11 21:19:39","inviteeNickname":"游客65770C6B3E4DE"}],"options":[]},"systemTimestamp":1702302267,"systemDateTime":"2023-12-11 21:44:27","msg":"OK"})
     */
    public function aiTempTypeList()
    {
        $param = $this->request()->getRequestParam();
        try {
            $data = [
                ['id' => 1, 'typeName' => '无码'],
                ['id' => 2, 'typeName' => 'SM捆绑'],
                ['id' => 3, 'typeName' => 'cosplay'],
                ['id' => 4, 'typeName' => '明星'],
                ['id' => 5, 'typeName' => '欧美'],
                ['id' => 6, 'typeName' => '写真'],
                ['id' => 7, 'typeName' => '模特'],
                ['id' => 8, 'typeName' => '直播'],
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}