<?php

namespace App\HttpController\Api\Admin\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\Upload;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Video\ShortVideoModel;
use App\Service\Oss\AwsOssService;
use App\Service\Oss\LocalOssService;
use App\Service\Video\ShortVideoService;
use App\Utility\Func;
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
use EasySwoole\Utility\File;
use Exception;
use Throwable;

/**
 * Class ShortVideo
 * @package App\HttpController\Api\Admin\Video
 * @ApiGroup(groupName="后台-短视频-短视频 Admin/Video/ShortVideo")
 * @ApiGroupDescription("后台短视频模块短视频相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class ShortVideo extends AdminBase
{
    /**
     * 短视频列表
     * @Api(name="短视频列表",path="/Api/Admin/Video/ShortVideo/shortVideoList")
     * @ApiDescription("短视频列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="vodId", alias="短视频id", type="int", optional="", min="1", description="短视频id")
     * @Param(name="vodName", alias="视频名", type="string", optional="", mbLengthMin="1", description="视频名")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["vodId_DESC", "vodId_ASC", "sort_ASC", "sort_DESC"], description="1.id倒叙（vodId_DESC）2.id正叙（vodId_ASC）3.sort正叙（sort_ASC） 4.sort倒叙（sort_DESC）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"vodId":1,"vodName":"测试1","vodPic":"/Public/test/1.png","vodPlayUrl":"/Public/test/1.m3u8","fileType":"up","likeCount":0,"sort":0,"status":1,"createTime":"2023-10-23 19:36:19","updateTime":"2023-10-23 19:36:21"}],"options":[]},"systemTimestamp":1698061158,"systemDateTime":"2023-10-23 19:39:18","msg":"OK"})
     */
    public function shortVideoList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['vodId']) && $keyword['vodId'] = $param['vodId'];
            isset($param['vodName']) && $keyword['vodName'] = $param['vodName'];
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'vodId',
                'vodName',
                'vodPic',
                'vodPlayUrl',
                'fileType',
                'realLikeCount',
                'sort',
                'status',
                'createTime',
                'updateTime',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ShortVideoModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 短视频详情
     * @Api(name="短视频详情",path="/Api/Admin/Video/ShortVideo/shortVideoDetail")
     * @ApiDescription("短视频详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="短视频id")
     * @ApiSuccess({"code":200,"result":{"vodId":1,"vodName":"测试1","vodPic":"/Public/test/1.png","vodPlayUrl":"/Public/test/1.m3u8","fileType":"up","likeCount":0,"sort":0,"status":1,"createTime":"2023-10-23 19:36:19","updateTime":"2023-10-23 19:36:21"},"systemTimestamp":1698061225,"systemDateTime":"2023-10-23 19:40:25","msg":"OK"})
     */
    public function shortVideoDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $article = ShortVideoModel::create()
                ->get([
                    'vodId' => $param['vodId'],
                    'status' => [ShortVideoModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $article, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 短视频添加
     * @Api(name="短视频添加",path="/Api/Admin/Video/ShortVideo/add")
     * @ApiDescription("短视频添加")
     * @Method(allow=["POST"])
     * @Param(name="vodName", alias="视频名", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="视频名")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="vodPic", alias="封面图", type="string", required="", mbLengthMin="0", description="封面图")
     * @Param(name="vodPlayUrl", alias="播放地址", type="string", required="", mbLengthMin="1", description="播放地址")
     * @Param(name="likeCount", alias="点赞数", type="int", required="", min="0", description="虚假点赞数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
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
                'is_recommod' => intval($param['is_recommod']),
            ];

            /* 处理图片路径 begin */
            $this->verifyAdParamStep2($data, $param);
            /* 处理图片路径 end */

            $result = ShortVideoService::getInstance()->addShortVideo($data);

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

    /**
     * 短视频编辑
     * @Api(name="短视频编辑",path="/Api/Admin/Video/ShortVideo/edit")
     * @ApiDescription("短视频编辑")
     * @Method(allow=["POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="短视频id")
     * @Param(name="vodName", alias="视频名", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="视频名")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="vodPic", alias="封面图", type="string", required="", mbLengthMin="0", description="封面图")
     * @Param(name="vodPlayUrl", alias="播放地址", type="string", required="", mbLengthMin="1", description="播放地址")
     * @Param(name="likeCount", alias="点赞数", type="int", required="", min="0", max="65535", description="虚假点赞数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'vodId' => intval($param['vodId']),
                'vodName' => trim($param['vodName']),
                'fileType' => trim($param['fileType']),
                'vodPic' => trim($param['vodPic']),
                'vodPlayUrl' => trim($param['vodPlayUrl']),
                'likeCount' => intval($param['likeCount']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'shortTag' => intval($param['shortTag']),
                'is_recommod' => intval($param['is_recommod']),
            ];

            // 这里获取的是当前数据，用作对比判断。
            $shortVideo = ShortVideoModel::create()->get($param['vodId']);
            if (!$shortVideo) {
                throw new Exception('无效的视频id', Status::CODE_BAD_REQUEST);
            }

            /* 处理图片路径 begin */
            if ($shortVideo['fileType'] != $param['fileType'] || $shortVideo['vodPic'] != $param['vodPic']) {
                $this->verifyAdParamStep2($data, $param);
            }
            /* 处理图片路径 end */

            $result = ShortVideoService::getInstance()->editShortVideo($data);

            // 最后要删除之前的老图片（如果有修改图片的话）
            if ($shortVideo['fileType'] != $param['fileType'] || $shortVideo['vodPic'] != $param['vodPic']) {
                switch ($shortVideo['fileType']) {
                    case ShortVideoModel::FILE_TYPE_UP:
                        LocalOssService::getInstance()->deleteObject($shortVideo['vodPic']);
                        break;
                    case ShortVideoModel::FILE_TYPE_AWS_S3:
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

    /**
     * 短视频修改状态
     * @Api(name="短视频修改状态",path="/Api/Admin/Video/ShortVideo/setStatus")
     * @ApiDescription("短视频修改状态")
     * @Method(allow=["POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="短视频id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'vodId' => $param['vodId'],
                'status' => intval($param['status']),
            ];

            $result = ShortVideoService::getInstance()->editShortVideo($data);

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
            $result = ShortVideoModel::create()->update($data,['vodId' => $param['vodId']]);

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
    /**
     * 短视频删除
     * @Api(name="短视频删除",path="/Api/Admin/Video/ShortVideo/delete")
     * @ApiDescription("短视频删除")
     * @Method(allow=["POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="短视频id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            $data['vodId'] = $param['vodId'];
            $data['status'] = ShortVideoModel::STATE_DELETED;

            $result = ShortVideoService::getInstance()->editShortVideo($data);

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
                case ShortVideoModel::FILE_TYPE_UP:
                    // 如果是上传的文件需要将临时文件转到广告目录
                    $data['vodPic'] = Func::moveTempFile($param['vodPic'], Upload::TYPE_VIDEO);
                    break;
                case ShortVideoModel::FILE_TYPE_AWS_S3:
                    // 亚马逊S3也是使用的相对路径，不需要处理本地图片。
                case ShortVideoModel::FILE_TYPE_URL:
                    $data['vodPic'] = $param['vodPic'];
                    break;
            }
        }
    }

    /**
     * 生成视频封面
     * @Api(name="生成视频封面",path="/Api/Admin/Video/ShortVideo/generateCover")
     * @ApiDescription("生成视频封面")
     * @Method(allow=["POST"])
     * @Param(name="vodId", alias="短视频id", type="int", required="", min="1", description="短视频id")
     * @apiSuccess({"code":200,"result":{"fileType":"awsS3","path":"/Upload/Image/video/1000/01/01/8a230ff9ad3d20630e9c4cf58ca00116.jpg"},"systemTimestamp":1698384807,"systemDateTime":"2023-10-27 13:33:27","msg":"OK"})
     */
    public function generateCover()
    {
        $param = $this->request()->getRequestParam();

        try {
            // 这里获取的是当前数据，用作对比判断。
            $shortVideo = ShortVideoModel::create()->get($param['vodId']);

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