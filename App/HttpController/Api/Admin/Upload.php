<?php

namespace App\HttpController\Api\Admin;

use App\Service\Oss\AwsOssService;
use App\Service\Oss\LocalOssService;
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
use App\Model\Common\ConfigModel;
/**
 * Class Upload
 * @package App\HttpController\Api\Admin
 * @ApiGroup(groupName="后台-公共-上传 Admin/Upload")
 * @ApiGroupDescription("后台上传相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Upload extends AdminBase
{

    /**
     * 上传图片
     * 重要：
     * 这里说明一下这个type，如果要在编辑操作的时候先将文件上传到临时目录，然后保存的时候再将文件转移到对应目录的话。
     * 上传的时候请选择type:temp参数。
     * @Api(name="上传图片",path="/Api/Admin/Upload/image")
     * @ApiDescription("上传图片，提交的文件的键:file")
     * @Method(allow=["POST"])
     * @Param(name="type", alias="上传图片类型", type="string", required="", inArray=["ad", "temp", "other", "article", "video", "photo", "live"], description="上传图片类型(temp临时,other其他,article文章,video短视频,photo照片)")
     * @apiSuccess({"code":200,"result":{"path":"/Upload/Image/ad/2023/06/03/45a4741b3b159f1265272e0929472d46.gif"},"systemTimestamp":1685792978,"systemDateTime":"2023-06-03 19:49:38","msg":"上传成功"})
     */
    public function image()
    {
        $param = $this->request()->getRequestParam();
        try {
            //$result = LocalOssService::getInstance()->uploadImage($this->request(), $param['type']);
            $result = AwsOssService::getInstance()->uploadImage($this->request(), $param['type']);
        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, '上传成功');
    }

   public function getImage(){
    $param = $this->request()->getRequestParam();
    try {
        if(!$this->isValidUrl($param['url'])){
            $config=ConfigModel::create()->where("cfgKey",["AwsS3Host","AwsS3Bucket"],"in")->all();
            $AwsS3Host="";
            $AwsS3Bucket="";
            foreach($config as $k=>$v){
                if($k["cfgKey"]=="AwsS3Host"){
                    $AwsS3Host=$v["cfgValue"];
                }
                if($k["cfgKey"]=="AwsS3Bucket"){
                    $AwsS3Bucket=$v["cfgValue"];
                }
            }
            $param['url']=$AwsS3Host.$AwsS3Bucket.$param['url'];
        }
        return $this->writeJson(Status::CODE_OK, $param['url'], '地址');
        $fileData=file_get_contents($param['url']);
       
        // $result = AwsOssService::getInstance()->uploadFile($this->request(), $param['type']);
    } catch (\Throwable $e) {
        return $this->writeJson($e->getCode(), [], $e->getMessage());
    }

    return $this->writeJson(Status::CODE_OK, "data:image/jpeg;base64,".$fileData, '上传成功');
   }
   public function isValidUrl($url) {
        $pattern = '/\b(?:https?|ftp):\/\/[a-z0-9-]+(\.[a-z0-9-]+)+\b(?:\/[^\s]*)?/i';
         return preg_match($pattern, $url) === 1;
    }
    /**
     * 上传图片到亚马逊s3
     * @Api(name="上传图片到亚马逊s3",path="/Api/Admin/Upload/awsImage")
     * @ApiDescription("上传图片到亚马逊s3，提交的文件的键:file")
     * @Method(allow=["POST"])
     * @Param(name="type", alias="上传图片类型", type="string", required="", inArray=["ad", "other", "article", "video", "photo", "live"], description="上传图片类型(ad广告,other其他,article文章,video短视频,photo照片)")
     * @apiSuccess({"code":200,"result":{"path":"/Upload/Image/ad/2023/06/03/45a4741b3b159f1265272e0929472d46.gif"},"systemTimestamp":1685792978,"systemDateTime":"2023-06-03 19:49:38","msg":"上传成功"})
     */
    public function awsImage()
    {
        $param = $this->request()->getRequestParam();
        try {
            $result = AwsOssService::getInstance()->uploadFile($this->request(), $param['type']);
        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, '上传成功');
    }
}