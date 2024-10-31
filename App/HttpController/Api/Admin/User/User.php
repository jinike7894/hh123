<?php

namespace App\HttpController\Api\Admin\User;
use App\Model\User\UserModel;
use App\HttpController\Api\Admin\AdminBase;
use EasySwoole\Http\Message\Status;
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
use App\Model\Merchant\ChannelModel;

use Exception;
use Throwable;

/**
 * Class User
 * @package App\HttpController\Api\Admin\User
 * @ApiGroup(groupName="后台-用户-用户 Admin/User/User")
 * @ApiGroupDescription("后台用户相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class User extends AdminBase
{
     //搜索用户
     public function list(){
          $param = $this->request()->getRequestParam();
        try {
            $model=UserModel::create();
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? 20);
            if(isset($param["userName"])){
                $model->where(["userName"=> ['%' . $param['userName'] . '%', 'LIKE']]);
            }
            if(isset($param["nickname"])){
                $model->where(["nickname"=> ['%' . $param['nickname'] . '%', 'LIKE']]);
            }
            if(isset($param["userId"])){
                $model->where(["userId"=>$param['userId']]);
            }
            $field = [
                '*',
            ];
            $data = $model
                ->order("userId","desc")
                ->getAll($page, $keyword, $pageSize, $field);
            if($data["list"]){
                $channelArray=[];
                foreach($data["list"] as $k=>$v){
                    $channelArray[]=$v->channelId;
                }
                $channelres=ChannelModel::create()->where("channelId",$channelArray,"in")->getAll();
                foreach($data["list"] as $dk=>$dv){
                    foreach($channelres["list"] as $ck=>$cv){
                        if($dv->channelId==$cv->channelId){
                            $data["list"][$k]->channelName=$cv->channelKey;
                        }
                    }
                }
              
            }
          
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
     }
     public function info(){
        $param = $this->request()->getRequestParam();
        try {
            $model=UserModel::create();
            $field = [
                '*',
            ];
            $data = $model
                ->where(["userId"=>$param["id"]])
                ->order("userId","desc")
                ->get();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
     }
     public function edit(){
        $param = $this->request()->getRequestParam();
      try {
          $model=UserModel::create();
          $data=[
            "userGroupId"=>$param["userGroupId"],
            "userGroupExpiryDate"=>$param["userGroupExpiryDate"],
            "nickname"=>$param["nickname"],
            "status"=>$param["status"],
          ];
          $data = $model
              ->update($data,["userId"=>$param["userId"]]);
      } catch (Throwable $e) {
          return $this->writeJson($e->getCode(), [], $e->getMessage());
      }

      return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
   }
}