<?php

namespace App\HttpController\Api\Admin\Live;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveModel;
use App\Model\Navigation\AdModel;
use App\Model\Prostitute\ProstituteModel;
use App\Service\Live\LiveService;
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
use Exception;
use Throwable;

/**
 * Class Live
 * @package App\HttpController\Api\Admin\Live
 * @ApiGroup(groupName="后台-直播-直播 Admin/Live/Live")
 * @ApiGroupDescription("后台直播相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Live extends AdminBase
{
    /**
     * 直播列表
     * @Api(name="直播列表",path="/Api/Admin/Live/Live/liveList")
     * @ApiDescription("直播列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="liveId", alias="直播id", type="int", optional="", min="1", description="直播id")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["liveId_DESC", "liveId_ASC", "sort_DESC", "sort_ASC"], description="1.直播id（liveId）2.排序（sort）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"liveId":1,"liveTitle":"直播标题","fileType":"awsS3","liveCover":"/Upload/Image/video/2023/12/01/ade5d6e2ee4533256517e254566996d4.jpg","liveUrl":"https://v.gsuus.com/play/negPEZ3a/index.m3u8","streamerNickname":"主播昵称","sort":0,"status":1,"adName":""}],"options":[]},"systemTimestamp":1702723866,"systemDateTime":"2023-12-16 18:51:06","msg":"OK"})
     */
    public function liveList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['liveId']) && $keyword['liveId'] = intval($param['liveId']);
            isset($param['status']) && $keyword['status'] = trim($param['status']);

            $field = [
                'liveId',
                'liveTitle',
                'fileType',
                'liveCover',
                'liveUrl',
                'streamerNickname',
                'sort',
                'status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = LiveModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = AdModel::create()->appendInfo($data['list'], ['adName'], 'adId', 'adId');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 直播详情
     * @Api(name="直播详情",path="/Api/Admin/Live/Live/liveDetail")
     * @ApiDescription("直播详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="liveId", alias="直播id", type="int", required="", min="1", description="直播id")
     * @ApiSuccess({"code":200,"result":{"liveId":1,"liveTitle":"直播标题","fileType":"awsS3","liveCover":"/Upload/Image/video/2023/12/01/ade5d6e2ee4533256517e254566996d4.jpg","liveViewers":3000,"liveUrl":"https://v.gsuus.com/play/negPEZ3a/index.m3u8","streamerNickname":"主播昵称","streamerAvatar":"/Upload/Image/photo/2023/12/14/41f543792eda180e42541c170cdbf7a9.jpeg","adId":1,"sort":0,"status":1,"createTime":"1000-01-01 00:00:00","updateTime":"2023-12-14 21:16:32"},"systemTimestamp":1702718839,"systemDateTime":"2023-12-16 17:27:19","msg":"OK"})
     */
    public function liveDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $vipGoods = LiveModel::create()
                ->get([
                    'liveId' => $param['liveId'],
                    'status' => [LiveModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $vipGoods, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 直播添加
     * @Api(name="直播添加",path="/Api/Admin/Live/Live/add")
     * @ApiDescription("直播添加")
     * @Method(allow=["POST"])
     * @Param(name="liveTitle", alias="直播标题", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="直播标题")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="liveCover", alias="直播封面", type="string", required="", mbLengthMin="0", description="直播封面")
     * @Param(name="liveViewers", alias="直播间人数", type="int", required="", min="0", description="直播间人数")
     * @Param(name="liveUrl", alias="直播链接", type="string", required="", mbLengthMin="0", description="直播链接")
     * @Param(name="streamerNickname", alias="主播昵称", type="string", required="", mbLengthMin="0", mbLengthMax="16", description="主播昵称")
     * @Param(name="streamerAvatar", alias="主播头像", type="string", required="", mbLengthMin="0", description="主播头像")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'liveTitle' => trim($param['liveTitle']),
                'fileType' => trim($param['fileType']),
                'liveCover' => trim($param['liveCover']),
                'liveViewers' => intval($param['liveViewers']),
                'liveUrl' => trim($param['liveUrl']),
                'streamerNickname' => trim($param['streamerNickname']),
                'streamerAvatar' => trim($param['streamerAvatar']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = LiveService::getInstance()->addLive($data);

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
     * 直播编辑
     * @Api(name="直播编辑",path="/Api/Admin/Live/Live/edit")
     * @ApiDescription("直播编辑")
     * @Method(allow=["POST"])
     * @Param(name="liveId", alias="直播id", type="int", required="", min="1", description="直播id")
     * @Param(name="liveTitle", alias="直播标题", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="直播标题")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="liveCover", alias="直播封面", type="string", required="", mbLengthMin="0", description="直播封面")
     * @Param(name="liveViewers", alias="直播间人数", type="int", required="", min="0", description="直播间人数")
     * @Param(name="liveUrl", alias="直播链接", type="string", required="", mbLengthMin="0", description="直播链接")
     * @Param(name="streamerNickname", alias="主播昵称", type="string", required="", mbLengthMin="0", mbLengthMax="16", description="主播昵称")
     * @Param(name="streamerAvatar", alias="主播头像", type="string", required="", mbLengthMin="0", description="主播头像")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'liveId' => intval($param['liveId']),
                'liveTitle' => trim($param['liveTitle']),
                'fileType' => trim($param['fileType']),
                'liveCover' => trim($param['liveCover']),
                'liveViewers' => intval($param['liveViewers']),
                'liveUrl' => trim($param['liveUrl']),
                'streamerNickname' => trim($param['streamerNickname']),
                'streamerAvatar' => trim($param['streamerAvatar']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = LiveService::getInstance()->editLive($data);

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
     * 直播修改状态
     * @Api(name="直播修改状态",path="/Api/Admin/Live/Live/setStatus")
     * @ApiDescription("直播修改状态")
     * @Method(allow=["POST"])
     * @Param(name="liveId", alias="直播id", type="int", required="", min="1", description="直播id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'liveId' => $param['liveId'],
                'status' => intval($param['status']),
            ];

            $result = LiveService::getInstance()->editLive($data);

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
     * 直播删除
     * @Api(name="直播删除",path="/Api/Admin/Live/Live/delete")
     * @ApiDescription("直播删除")
     * @Method(allow=["POST"])
     * @Param(name="liveId", alias="直播id", type="int", required="", min="1", description="直播id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'liveId' => $param['liveId'],
                'status' => LiveModel::STATE_DELETED,
            ];

            $result = LiveService::getInstance()->editLive($data);

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
}