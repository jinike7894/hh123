<?php
namespace App\HttpController\Api\Admin\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\Upload;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Video\VideoModel;
use App\Service\Oss\AwsOssService;
use App\Service\Oss\LocalOssService;
use App\Service\Video\ShortVideoService;
use App\Utility\Func;
use EasySwoole\Http\Message\Status;
use Exception;
use Throwable;


class Video extends AdminBase
{
   
    public function list()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            isset($param['vodId']) && $keyword['vodId'] = $param['vodId'];
            isset($param['vodName']) && $keyword['vodName'] = $param['vodName'];
            isset($param['vod_status']) && $keyword['vod_status'] = intval($param['status']);
            $field = [
                '*',
            ];
            $sortType = $param['type_id'] ?? '';
            $data = VideoModel::create()
                ->where(["vod_status"=>1])
                ->order("vod_id","desc")
                ->getAll($page, $keyword, $pageSize, $field);
            // if($data["list"]){
            //     // foreach($data["list"] as $k=>$v){
            //     //     // $data["list"][$k]=$this->convertKeysToCamelCase($v);
            //     // }
            // }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取视频详情
    public function VideoDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $article = VideoModel::create()
                ->get([
                    'vodId' => $param['vodId'],
                    // 'status' => [VideoModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $article, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'vodName' => trim($param['vodName']), //名称
                'fileType' => trim($param['fileType']), //url:远程图片awsS3:s3类型
                'vodPic' => trim($param['vodPic']), //封面
                'vodPlayUrl' => trim($param['vodPlayUrl']),  //播放地址
                'likeCount' => intval($param['likeCount']),  //点赞量
                'sort' => intval($param['sort']), //排序
                'status' => intval($param['status']), //是否开启
                'type_id' => intval($param['type_id']),//分类id
                'is_recommod' => intval($param['is_recommod']),//是否推荐
            ];

            /* 处理图片路径 begin */
            $this->verifyAdParamStep2($data, $param);
            /* 处理图片路径 end */

            $result = VideoModel::create($data)->save();

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
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'vodName' => trim($param['vodName']),
                'fileType' => trim($param['fileType']),
                'vodPic' => trim($param['vodPic']),
                'vodPlayUrl' => trim($param['vodPlayUrl']),
                'likeCount' => intval($param['likeCount']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'shortTag' => intval($param['shortTag']),
                // 'is_recommod' => intval($param['is_recommod']),
            ];

            // 这里获取的是当前数据，用作对比判断。
            $shortVideo = VideoModel::create()->get($param['vodId']);
            if (!$shortVideo) {
                throw new Exception('无效的视频id', Status::CODE_BAD_REQUEST);
            }

            /* 处理图片路径 begin */
            if ($shortVideo['fileType'] != $param['fileType'] || $shortVideo['vodPic'] != $param['vodPic']) {
                $this->verifyAdParamStep2($data, $param);
            }
            /* 处理图片路径 end */
            $result = VideoModel::create()->update($data,["vodId"=>intval($param['vodId'])]);
            // 最后要删除之前的老图片（如果有修改图片的话）
            if ($shortVideo['fileType'] != $param['fileType'] || $shortVideo['vodPic'] != $param['vodPic']) {
                switch ($shortVideo['fileType']) {
                    case VideoModel::FILE_TYPE_UP:
                        LocalOssService::getInstance()->deleteObject($shortVideo['vodPic']);
                        break;
                    case VideoModel::FILE_TYPE_AWS_S3:
                        AwsOssService::getInstance()->deleteObject($shortVideo['vodPic']);
                        break;
                }
            }

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
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'status' => intval($param['status']),
            ];

            $result = VideoModel::create()->update($data,["vodId"=>$param['vodId']]);

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
    //设置推荐
    public function setRecommod(){

        $param = $this->request()->getRequestParam();
        try {
            $data = [   
                'is_recommod' => intval($param['is_recommod']),
            ];
            $result = VideoModel::create()->update($data,['vodId' => $param['vodId']]);

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
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            $data['status'] = VideoModel::STATE_DELETED;

            $result = VideoModel::create()->update($data,["vodId"=>$param['vodId']]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_DELETE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
    private function verifyAdParamStep2(&$data, $param)
    {
        // 2023-10-26 因为图片可以留空，则需要判断一下。
        if($param['vodPic']){
            switch ($param['fileType']) {
                case VideoModel::FILE_TYPE_UP:
                    // 如果是上传的文件需要将临时文件转到广告目录
                    $data['vodPic'] = Func::moveTempFile($param['vodPic'], Upload::TYPE_VIDEO);
                    break;
                case VideoModel::FILE_TYPE_AWS_S3:
                    // 亚马逊S3也是使用的相对路径，不需要处理本地图片。
                case VideoModel::FILE_TYPE_URL:
                    $data['vodPic'] = $param['vodPic'];
                    break;
            }
        }
    }
    //视频生成封面---可以废弃
    public function generateCover()
    {
        $param = $this->request()->getRequestParam();

        try {
            // 这里获取的是当前数据，用作对比判断。
            $shortVideo = VideoModel::create()->get($param['vodId']);

            if (!$shortVideo) {
                throw new Exception('无效的视频id', Status::CODE_BAD_REQUEST);
            }

            $result = ShortVideoService::getInstance()->generateCover($shortVideo);
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
}