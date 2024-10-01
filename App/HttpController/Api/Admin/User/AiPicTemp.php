<?php

namespace App\HttpController\Api\Admin\User;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveModel;
use App\Model\Navigation\AdModel;
use App\Model\Prostitute\ProstituteModel;
use App\Model\User\UserAiPicTempModel;
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
 * @package App\HttpController\Api\Admin\AiPicTemp
 * @ApiGroup(groupName="后台-图片模版 Admin/Live/AiPicTemp")
 * @ApiGroupDescription("后台图片模版相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class AiPicTemp extends AdminBase
{
    /**
     * ai换脸图片模版-新
     * @Api(name="ai换脸图片模版列表-新",path="/Api/Admin/User/AiPicTemp/tempList")
     * @ApiDescription("ai换脸图片模版列表-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({})
     */
    public function tempList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $field = [
                'pictempId',
                'imgCode',
                'imgPrice',
                'imgPath',
                'imgName',
                'imgWidth',
                'imgHeight',
                'status',
            ];

            $data = UserAiPicTempModel::create()
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai换脸图片模版详情-新
     * @Api(name="ai换脸图片模版详情-新",path="/Api/Admin/User/AiPicTemp/tempDetail")
     * @ApiDescription("ai换脸图片模版详情-新")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pictempId", alias="图片模版id", type="int", required="", min="1", description="图片模版id")
     * @ApiSuccess({"code":200,"result":{"liveId":1,"liveTitle":"直播标题","fileType":"awsS3","liveCover":"/Upload/Image/video/2023/12/01/ade5d6e2ee4533256517e254566996d4.jpg","liveViewers":3000,"liveUrl":"https://v.gsuus.com/play/negPEZ3a/index.m3u8","streamerNickname":"主播昵称","streamerAvatar":"/Upload/Image/photo/2023/12/14/41f543792eda180e42541c170cdbf7a9.jpeg","adId":1,"sort":0,"status":1,"createTime":"1000-01-01 00:00:00","updateTime":"2023-12-14 21:16:32"},"systemTimestamp":1702718839,"systemDateTime":"2023-12-16 17:27:19","msg":"OK"})
     */
    public function tempDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $temp = UserAiPicTempModel::create()
                ->get([
                    'pictempId' => $param['pictempId'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $temp, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * ai换脸图片模版编辑-新
     * @Api(name="ai换脸图片模版编辑-新",path="/Api/Admin/User/AiPicTemp/edit")
     * @ApiDescription("ai换脸图片模版编辑-新")
     * @Method(allow=["POST"])
     * @Param(name="pictempId", alias="模版id", type="int", required="", min="1", description="模版id")
     * @Param(name="imgName", alias="图片名称", type="string", required="", description="图片名称")
     * @Param(name="status", alias="状态", type="int", required="", description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'imgName' => trim($param['imgName']),
                'status' => trim($param['status']),
            ];

            $temp = UserAiPicTempModel::create()->get([
                'pictempId' => $param['pictempId'],
            ]);
            if (!$temp) {
                throw new Exception('无效的模版id', Status::CODE_BAD_REQUEST);
            }
            $result = $temp->update($data);

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