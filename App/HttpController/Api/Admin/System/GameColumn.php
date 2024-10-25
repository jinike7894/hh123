<?php
namespace App\HttpController\Api\Admin\User;
use App\Model\GameColumn\GameColumn as game;
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
use App\HttpController\Api\Admin\Upload as uploadNew;
use Exception;
use Throwable;

class GameColumn extends AdminBase
{

     public function info(){
          $param = $this->request()->getRequestParam();

        try {
           
            $data = game::create()
                ->where(["id"=>1])
                ->get();
                $imgData=new uploadNew();
                $data["img_show"]=$imgData->getUrlImage($data["img"]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
     }

     public function edit(){
        $param = $this->request()->getRequestParam();
      try {
          $model=game::create();
          $data=[
            "url"=>$param["url"],
            "img"=>$param["img"],
            "fileType"=>$param["fileType"],
          ];
          $data = $model
              ->update($data,["id"=>$param["id"]]);
      } catch (Throwable $e) {
          return $this->writeJson($e->getCode(), [], $e->getMessage());
      }

      return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
   }
}