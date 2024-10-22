<?php

namespace App\HttpController\Api\Live;

use App\Enum\ConfigKey\LiveConfigKey;
use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\User\UserBase;
use App\Model\Common\ConfigModel;
use App\Model\Live\LiveModel;
use App\Model\Navigation\AdModel;
use App\Model\Prostitute\ProstituteModel;
use App\Model\Prostitute\ProstituteTypeModel;
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
use Exception;
use Throwable;

/**
 * Class Live
 * @package App\HttpController\Api\Live
 * @ApiGroup(groupName="直播 Live/Live")
 * @ApiGroupDescription("直播相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Live extends UserBase
{
    /**
     * 直播列表
     * @Api(name="直播列表",path="/Api/Live/Live/liveList")
     * @ApiDescription("直播列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["sort_DESC", "liveId_DESC"], description="1.sort 权重排序 2.liveId按发布时间")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"liveId":1,"liveTitle":"直播标题","fileType":"awsS3","liveCover":"/Upload/Image/video/2023/12/01/ade5d6e2ee4533256517e254566996d4.jpg","liveViewers":3000,"streamerNickname":"主播昵称"}],"options":{"status":1}},"systemTimestamp":1702559800,"systemDateTime":"2023-12-14 21:16:40","msg":"OK"})
     */
    public function liveList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['status'] = LiveModel::STATE_NORMAL;

            $field = [
               "*"
            ];

            $sortType = $param['sortType'] ?? 'liveId_DESC';
            $data = LiveModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            //分页还是按照正常的顺序分页，但是返回的列表打乱一下顺序保证每次都不一样。
            shuffle($data['list']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 直播详情
     * @Api(name="直播详情",path="/Api/Live/Live/liveDetail")
     * @ApiDescription("直播详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="liveId", alias="直播id", type="int", required="", min="1", description="直播id")
     * @ApiSuccess({"code":200,"result":{"liveDetail":{"liveId":1,"liveTitle":"直播标题","fileType":"awsS3","liveCover":"/Upload/Image/video/2023/12/01/ade5d6e2ee4533256517e254566996d4.jpg","liveViewers":3000,"liveUrl":"https://v.gsuus.com/play/negPEZ3a/index.m3u8","streamerNickname":"主播昵称","streamerAvatar":"/Upload/Image/photo/2023/12/14/41f543792eda180e42541c170cdbf7a9.jpeg"},"announcement":"直播公告"},"systemTimestamp":1702723441,"systemDateTime":"2023-12-16 18:44:01","msg":"OK"})
     */
    public function liveDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $live = LiveModel::create()
                ->field([
                    'liveId',
                    'liveTitle',
                    'fileType',
                    'liveCover',
                    'liveViewers',
                    'liveUrl',
                    'streamerNickname',
                    'streamerAvatar',
                ])
                ->get([
                    'liveId' => $param['liveId'],
                    'status' => LiveModel::STATE_NORMAL,
                ]);

            if (!$live) {
                throw new Exception('无效的直播id', Status::CODE_BAD_REQUEST);
            }

            $announcement = ConfigModel::create()->getConfigValue(LiveConfigKey::ANNOUNCEMENT);
            $data = [
                'liveDetail' => $live,
                'announcement' => $announcement,
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}