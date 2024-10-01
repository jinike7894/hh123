<?php

namespace App\HttpController\Api\Admin\System;

use App\Enum\ConfigKey\WebsiteConfigKey;
use App\Enum\Upload;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Common\ConfigModel;
use App\Service\Oss\LocalOssService;
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
 * Class Website
 * @package App\HttpController\Api\Admin\System
 * @ApiGroup(groupName="后台-系统-网站 Admin/System/Website")
 * @ApiGroupDescription("后台系统设置相关。设置配置相关的参数key是大驼峰命名，这里特殊的要注意一下。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Website extends AdminBase
{
    /**
     * 获取网站配置列表
     * @Api(name="获取网站配置列表",path="/Api/Admin/System/Website/configList")
     * @ApiDescription("获取网站配置列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"cfgKey":"WebsiteTitle","cfgValue":"ES导航","title":"网站标题","description":"title 的值"},{"cfgKey":"WebsiteKeywords","cfgValue":"ES导航关键字","title":"网站关键字","description":"meta keywords 的值"},{"cfgKey":"WebsiteDescription","cfgValue":"ES导航描述","title":"网站描述","description":"meta description 的值"},{"cfgKey":"WebsiteContact","cfgValue":"广告联系TG:XX","title":"联系人","description":"网站联系人"},{"cfgKey":"CDN","cfgValue":"","title":"cdn地址","description":"cdn地址"},{"cfgKey":"Favicon","cfgValue":"","title":"网站图标","description":"网站图标"},{"cfgKey":"WebsiteCustomerService","cfgValue":"","title":"网站客服联系地址","description":"网站客服联系地址"},{"cfgKey":"WebsiteContactGroup","cfgValue":"","title":"网站联系群组地址","description":"网站联系群组地址"},{"cfgKey":"MainAnnouncement","cfgValue":"主公告","title":"主公告","description":"主公告"}],"systemTimestamp":1703517838,"systemDateTime":"2023-12-25 23:23:58","msg":"OK"})
     */
    public function configList()
    {
        try {
            $result = ConfigModel::create()
                ->field(['cfgKey', 'cfgValue', 'title', 'description'])
                ->where(['cfgKey' => [WebsiteConfigKey::ALL_KEY, 'IN']])
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 设置网站配置
     * @Api(name="设置网站配置",path="/Api/Admin/System/Website/setConfig")
     * @ApiDescription("设置网站配置")
     * @Method(allow=["POST"])
     * @Param(name="WebsiteTitle", alias="网站标题", type="string", optional="", description="网站标题")
     * @Param(name="WebsiteKeywords", alias="网站关键字", type="string", optional="", description="网站关键字")
     * @Param(name="WebsiteDescription", alias="网站描述", type="string", optional="", description="网站描述")
     * @Param(name="WebsiteContact", alias="联系人", type="string", optional="", description="联系人")
     * @Param(name="CDN", alias="cdn地址", type="string", optional="", description="cdn地址")
     * @Param(name="WebsiteStatisticEnabled", alias="网站统计扣量控制开关", type="string", optional="", description="网站统计扣量控制开关")
     * @Param(name="WebsiteStatisticConfig", alias="网站统计扣量控制配置", type="string", optional="", description="网站统计扣量控制配置")
     * @Param(name="Favicon", alias="网站图标", type="string", optional="", description="网站图标")
     * @Param(name="WebsiteCustomerService", alias="网站客服联系地址", type="string", optional="", description="网站客服联系地址")
     * @Param(name="WebsiteContactGroup", alias="网站联系群组地址", type="string", optional="", description="网站联系群组地址")
     * @Param(name="MainAnnouncement", alias="主公告", type="string", optional="", description="主公告")
     * @Param(name="GameNotify", alias="游戏跑马灯公告", type="string", optional="", description="游戏跑马灯公告")
     * @Param(name="AppNotify", alias="跑马灯公告", type="string", optional="", description="跑马灯公告")
     * @Param(name="RecommendUrl", alias="推荐网址", type="string", optional="", description="推荐网址")
     * @Param(name="SpareUrl", alias="备用网址", type="string", optional="", description="备用网址")
     * @Param(name="EMail", alias="邮箱", type="string", optional="", description="邮箱")
     * @Param(name="PermanentUrl", alias="永久网址", type="string", optional="", description="永久网址")
     * @ApiSuccess({"code":200,"result":4,"systemTimestamp":1685500974,"systemDateTime":"2023-05-31 10:42:54","msg":"OK"})
     */

    const Recommend_Url = 'RecommendUrl';
    const Spare_Url = 'SpareUrl';
    const E_Mail = 'EMail';
    const Permanent_Url = 'PermanentUrl';

    public function setConfig()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [];
            isset($param['WebsiteTitle']) && $data['WebsiteTitle'] = $param['WebsiteTitle'];
            isset($param['WebsiteKeywords']) && $data['WebsiteKeywords'] = $param['WebsiteKeywords'];
            isset($param['WebsiteDescription']) && $data['WebsiteDescription'] = $param['WebsiteDescription'];
            isset($param['WebsiteContact']) && $data['WebsiteContact'] = $param['WebsiteContact'];
            isset($param['CDN']) && $data['CDN'] = $param['CDN'];
            isset($param['Favicon']) && $data['Favicon'] = $param['Favicon'];

            // 2023-10-26 将统计代码扣量的配置写到了每一个页面中
            // isset($param['WebsiteStatisticEnabled']) && $data['WebsiteStatisticEnabled'] = $param['WebsiteStatisticEnabled'];
            // isset($param['WebsiteStatisticConfig']) && $data['WebsiteStatisticConfig'] = $param['WebsiteStatisticConfig'];

            // 2023-10-31 增加客服和群组地址的配置
            isset($param['WebsiteCustomerService']) && $data['WebsiteCustomerService'] = $param['WebsiteCustomerService'];
            isset($param['WebsiteContactGroup']) && $data['WebsiteContactGroup'] = $param['WebsiteContactGroup'];

            // 2023-12-25 增加主公告配置
            isset($param['MainAnnouncement']) && $data['MainAnnouncement'] = $param['MainAnnouncement'];

            // 2024-01-23 增加游戏跑马灯公告配置
            isset($param['GameNotify']) && $data['GameNotify'] = $param['GameNotify'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }
            // 2024-02-15 增加跑马灯公告配置
            isset($param['AppNotify']) && $data['AppNotify'] = $param['AppNotify'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            isset($param['RecommendUrl']) && $data['RecommendUrl'] = $param['RecommendUrl'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            isset($param['SpareUrl']) && $data['SpareUrl'] = $param['SpareUrl'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            isset($param['EMail']) && $data['EMail'] = $param['EMail'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            isset($param['PermanentUrl']) && $data['PermanentUrl'] = $param['PermanentUrl'];
            if (!$data) {
                throw new Exception('无有效数据', Status::CODE_BAD_REQUEST);
            }

            // 这里有个特殊的判断，如果favicon之前有数据，则要判断是否有图需要删除。
            $favicon = ConfigModel::create()->getConfigValue(WebsiteConfigKey::FAVICON);
            if (isset($param['Favicon']) && $param['Favicon'] != $favicon) {
                if ($favicon) {
                    LocalOssService::getInstance()->deleteObject($favicon);
                }

                // 并且将临时文件转移到other目录
                // 这里判断是因为可能是清除图片的操作，虽然有key，但是值为空，仅删除旧图片。所以在有值的时候才会转移。
                if ($data['Favicon']) {
                    $data['Favicon'] = Func::moveTempFile($data['Favicon'], Upload::TYPE_OTHER);
                }
            }

            $result = ConfigModel::create()->setConfig($data);

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