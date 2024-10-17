<?php
namespace App\HttpController\Api\Admin\Video;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\Upload;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Video\ShortVideoDyUserModel;
use App\Service\Oss\AwsOssService;
use App\Service\Oss\LocalOssService;
use App\Service\Video\ShortVideoService;
use App\Utility\Func;
use EasySwoole\Http\Message\Status;
use App\HttpController\Api\Admin\Upload as uploadNew;
use Exception;
use Throwable;


class ShortVideoDyUser extends AdminBase
{
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $model=ShortVideoDyUserModel::create();
            $keyword = [];
            if(isset($param['keyword'])||$param['keyword']){
                $model->where(['name' => ['%' . $param['name'] . '%', 'LIKE']]);  
            }
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'username',
                'img_src',
                'create_at',
                'update_at',
            ];
            $data = $model
                ->where(["is_del"=>ShortVideoDyUserModel::NODELETE])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
            if($data["list"]){
                foreach($data["list"] as $k=>$v){
                    $data["list"][$k]->create_at=date("Y-m-d H:i:s",$v->create_at);
                }
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
   //个人详细
    public function info()
    {
        $param = $this->request()->getRequestParam();

        try {
            $video = ShortVideoDyUserModel::create()
                ->get([
                    'id' => $param['id']
                ]);
            $imgData=new uploadNew();
            $video["img_show"]=$imgData->getUrlImage($video["img_src"]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $video, Status::getReasonPhrase(Status::CODE_OK));
    }
    //添加
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'username' => trim($param['username']),
                'img_src' => trim($param['img_src']),
                "is_del"=>0,
                "create_at"=>time(),
                "update_at"=>time(),
            ];
            $result = ShortVideoDyUserModel::create($data)->save();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_ADD,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
    //编辑
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'username' => intval($param['username']),
                'img_src' => trim($param['img_src']),
                'is_del' => intval($param['is_del']),
                'update_at' => time(),
            ];

            // 这里获取的是当前数据，用作对比判断。
            // $shortVideo = ShortVideoDyUserModel::create()->get(["id"=>$param["id"]]);
            // if (!$shortVideo) {
            //     throw new Exception('无效的id', Status::CODE_BAD_REQUEST);
            // }
            //预留要删除的图片
            $result=ShortVideoDyUserModel::create()->update($data,["id"=>$param["id"]]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
    //删除
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'is_del' => ShortVideoDyUserModel::DELETED,
                'update_at' => time(),
            ];
            $result=ShortVideoDyUserModel::create()->update($data,["id"=>$param["id"]]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
  
    // public function setStatus()
    // {
    //     $param = $this->request()->getRequestParam();

    //     try {
    //         $data = [
    //             'vodId' => $param['vodId'],
    //             'status' => intval($param['status']),
    //         ];

    //         $result = ShortVideoService::getInstance()->editShortVideo($data);

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }

    //     return $this->writeJson(
    //         Status::CODE_OK,
    //         $result,
    //         Status::getReasonPhrase(Status::CODE_OK),
    //         AdminLogsModel::TYPE_UPDATE,
    //         json_encode($param, JSON_UNESCAPED_UNICODE)
    //     );
    // }
    //设置推荐
    // public function setRecommod(){

    //     $param = $this->request()->getRequestParam();
    //     try {
    //         $data = [   
    //             'is_recommod' => intval($param['is_recommod']),
    //         ];
    //         $result = ShortVideoModel::create()->update($data,['vodId' => $param['vodId']]);

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }

    //     return $this->writeJson(
    //         Status::CODE_OK,
    //         $result,
    //         Status::getReasonPhrase(Status::CODE_OK),
    //         AdminLogsModel::TYPE_UPDATE,
    //         json_encode($param, JSON_UNESCAPED_UNICODE)
    //     );

    // }

    // public function delete()
    // {
    //     $param = $this->request()->getRequestParam();

    //     try {
    //         $data = [];
    //         $data['vodId'] = $param['vodId'];
    //         $data['status'] = ShortVideoModel::STATE_DELETED;

    //         $result = ShortVideoService::getInstance()->editShortVideo($data);

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }

    //     return $this->writeJson(
    //         Status::CODE_OK,
    //         $result,
    //         Status::getReasonPhrase(Status::CODE_OK),
    //         AdminLogsModel::TYPE_DELETE,
    //         json_encode($param, JSON_UNESCAPED_UNICODE)
    //     );
    // }
    // private function verifyAdParamStep2(&$data, $param)
    // {
    //     // 2023-10-26 因为图片可以留空，则需要判断一下。
    //     if($param['vodPic']){
    //         switch ($param['fileType']) {
    //             case ShortVideoModel::FILE_TYPE_UP:
    //                 // 如果是上传的文件需要将临时文件转到广告目录
    //                 $data['vodPic'] = Func::moveTempFile($param['vodPic'], Upload::TYPE_VIDEO);
    //                 break;
    //             case ShortVideoModel::FILE_TYPE_AWS_S3:
    //                 // 亚马逊S3也是使用的相对路径，不需要处理本地图片。
    //             case ShortVideoModel::FILE_TYPE_URL:
    //                 $data['vodPic'] = $param['vodPic'];
    //                 break;
    //         }
    //     }
    // }
    // public function generateCover()
    // {
    //     $param = $this->request()->getRequestParam();

    //     try {
    //         // 这里获取的是当前数据，用作对比判断。
    //         $shortVideo = ShortVideoModel::create()->get($param['vodId']);

    //         if (!$shortVideo) {
    //             throw new Exception('无效的视频id', Status::CODE_BAD_REQUEST);
    //         }

    //         $result = ShortVideoService::getInstance()->generateCover($shortVideo);
    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }
    //     return $this->writeJson(
    //         Status::CODE_OK,
    //         $result,
    //         Status::getReasonPhrase(Status::CODE_OK),
    //         AdminLogsModel::TYPE_UPDATE,
    //         json_encode($param, JSON_UNESCAPED_UNICODE)
    //     );
    // }
}