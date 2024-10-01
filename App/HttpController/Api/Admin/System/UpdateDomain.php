<?php

namespace App\HttpController\Api\Admin\System;

use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Service\Admin\UpdateDomainService;
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
 * Class UpdateDomain
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-基本设置 Admin/System/UpdateDomain")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="批量替换域名")
 */
class UpdateDomain extends AdminBase
{
    /**
     * 获取域名配置列表
     * @Api(name="获取域名配置列表",path="/Api/Admin/System/UpdateDomain/configList")
     * @ApiDescription("获取域名配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"title":"长影片域名替换","typeKey":"vodPlayUrl","params":[{"title":"原域名","key":"oldHost"},{"title":"新域名","key":"newHost"}]},{"title":"影片封面域名替换","typeKey":"vodPic","params":[{"title":"原域名","key":"oldHost"},{"title":"新域名","key":"newHost"}]},{"title":"短视频域名替换","typeKey":"shortVodPlayUrl","params":[{"title":"原域名","key":"oldHost"},{"title":"新域名","key":"newHost"}]},{"title":"短视频封面域名替换","typeKey":"shortVodPic","params":[{"title":"原域名","key":"oldHost"},{"title":"新域名","key":"newHost"}]}],"systemTimestamp":1703740697,"systemDateTime":"2023-12-28 13:18:17","msg":"OK"})
     */
    public function configList()
    {
        $vooDomain = [
            'title' => '长影片域名替换',
            'typeKey' => 'vodPlayUrl',
            'params' => [
                [
                    'title' => '原域名',
                    'key' => 'oldHost',
                ],
                [
                    'title' => '新域名',
                    'key' => 'newHost',
                ],
            ],
        ];

        $vodPic = [
            'title' => '影片封面域名替换',
            'typeKey' => 'vodPic',
            'params' => [
                [
                    'title' => '原域名',
                    'key' => 'oldHost',
                ],
                [
                    'title' => '新域名',
                    'key' => 'newHost',
                ],
            ],

        ];

        $shortVodDomain = [
            'title' => '短视频域名替换',
            'typeKey' => 'shortVodPlayUrl',
            'params' => [
                [
                    'title' => '原域名',
                    'key' => 'oldHost',
                ],
                [
                    'title' => '新域名',
                    'key' => 'newHost',
                ],
            ],

        ];

        $shortVodPic = [
            'title' => '短视频封面域名替换',
            'typeKey' => 'shortVodPic',
            'params' => [
                [
                    'title' => '原域名',
                    'key' => 'oldHost',
                ],
                [
                    'title' => '新域名',
                    'key' => 'newHost',
                ],
            ],

        ];
        $result = [
            $vooDomain,
            $vodPic,
            $shortVodDomain,
            $shortVodPic
        ];
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 批量更新域名
     * @Api(name="批量更新域名",path="/Api/Admin/System/UpdateDomain/setConfig")
     * @ApiDescription("批量更新域名")
     * @Method(allow=["POST"])
     * @Param(name="typeKey", alias="类型", type="string",required="", optional="", description="类型")
     * @Param(name="oldHost", alias="老域名", type="string",required="", optional="", description="老域名")
     * @Param(name="newHost", alias="新域名", type="string",required="", optional="", description="新域名")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1703742007,"systemDateTime":"2023-12-28 13:40:07","msg":"ok"})
     */
    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            $result = false;
            isset($param['typeKey']) && $data['typeKey'] = $param['typeKey'];
            isset($param['oldHost']) && $data['oldHost'] = $param['oldHost'];
            isset($param['newHost']) && $data['newHost'] = $param['newHost'];

            if (count($data) < 3) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }
            if ($data['typeKey'] == 'vodPlayUrl') {
                $result = UpdateDomainService::getInstance()->updateVodUrl($data['oldHost'], $data['newHost']);
            }
            if ($data['typeKey'] == 'vodPic') {
                $result = UpdateDomainService::getInstance()->updateVodPic($data['oldHost'], $data['newHost']);
            }
            if ($data['typeKey'] == 'shortVodPlayUrl') {
                $result = UpdateDomainService::getInstance()->updateShortVodUrl($data['oldHost'], $data['newHost']);
            }
            if ($data['typeKey'] == 'shortVodPic') {
                $result = UpdateDomainService::getInstance()->updateShortVodPic($data['oldHost'], $data['newHost']);
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
}