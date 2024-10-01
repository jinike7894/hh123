<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageTemplateModel;
use App\Model\Navigation\PageTemplateZoneRelationModel;
use App\Service\Navigation\PageService;
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
 * Class PageTemplate
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-页面模板 Admin/Navigation/PageTemplate")
 * @ApiGroupDescription("后台广告组相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class PageTemplate extends AdminBase
{
    /**
     * 页面模板列表
     * @Api(name="页面模板列表",path="/Api/Admin/Navigation/PageTemplate/templateList")
     * @ApiDescription("页面模板列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @apiSuccess({"code":200,"result":{"total":3,"list":[{"pageTemplateId":3,"pageTemplateName":"模板1","pageTemplateKey":"template1","description":"banner和tab换位置"},{"pageTemplateId":2,"pageTemplateName":"默认_测试","pageTemplateKey":"defaultTest","description":"tab的游戏换成赚钱"},{"pageTemplateId":1,"pageTemplateName":"默认","pageTemplateKey":"default","description":"默认样式"}],"options":[]},"systemTimestamp":1687604713,"systemDateTime":"2023-06-24 19:05:13","msg":"OK"})
     */
    public function templateList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $field = ['pageTemplateId', 'pageTemplateName', 'pageTemplateKey', 'description'];

            $data = PageTemplateModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 页面模板详情
     * @Api(name="页面模板详情",path="/Api/Admin/Navigation/PageTemplate/templateDetail")
     * @ApiDescription("页面模板详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageTemplateId", alias="模板id", type="int", required="", min="1", description="模板id")
     * @apiSuccess({"code":200,"result":[{"pageTemplateZoneRelationId":1,"zoneId":1,"status":1,"sort":0,"adGroupId":1,"zoneName":"顶部浮动","zoneKey":"topFloat","adGroupName":"顶部浮动","adGroupAlias":"","adGroupKey":"topFloat"},{"pageTemplateZoneRelationId":2,"zoneId":2,"status":1,"sort":0,"adGroupId":2,"zoneName":"横幅","zoneKey":"banner","adGroupName":"横幅","adGroupAlias":"","adGroupKey":"banner"},{"pageTemplateZoneRelationId":3,"zoneId":3,"status":1,"sort":10,"adGroupId":3,"zoneName":"标签页","zoneKey":"tab","adGroupName":"tab热门","adGroupAlias":"热门","adGroupKey":"tabHot"},{"pageTemplateZoneRelationId":4,"zoneId":3,"status":1,"sort":20,"adGroupId":4,"zoneName":"标签页","zoneKey":"tab","adGroupName":"tab视频","adGroupAlias":"视频","adGroupKey":"tabVideo"},{"pageTemplateZoneRelationId":5,"zoneId":3,"status":1,"sort":30,"adGroupId":5,"zoneName":"标签页","zoneKey":"tab","adGroupName":"tab直播","adGroupAlias":"直播","adGroupKey":"tabLive"},{"pageTemplateZoneRelationId":6,"zoneId":3,"status":1,"sort":40,"adGroupId":6,"zoneName":"标签页","zoneKey":"tab","adGroupName":"tab游戏","adGroupAlias":"游戏","adGroupKey":"tabGame"},{"pageTemplateZoneRelationId":7,"zoneId":4,"status":1,"sort":0,"adGroupId":7,"zoneName":"推荐","zoneKey":"recommend","adGroupName":"推荐","adGroupAlias":"下载推荐","adGroupKey":"recommend"},{"pageTemplateZoneRelationId":8,"zoneId":5,"status":1,"sort":0,"adGroupId":8,"zoneName":"约会","zoneKey":"date","adGroupName":"约会","adGroupAlias":"","adGroupKey":"date"},{"pageTemplateZoneRelationId":9,"zoneId":6,"status":1,"sort":0,"adGroupId":9,"zoneName":"底部浮动","zoneKey":"bottomFloat","adGroupName":"底部浮动","adGroupAlias":"","adGroupKey":"bottomFloat"}],"systemTimestamp":1687001604,"systemDateTime":"2023-06-17 19:33:24","msg":"OK"})
     */
    public function templateDetail()
    {
        $param = $this->request()->getRequestParam();

        try {

            $zoneTempList = PageTemplateZoneRelationModel::create()->getTemplateZone($param['pageTemplateId']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $zoneTempList, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 模板修改状态
     * @Api(name="模板修改状态",path="/Api/Admin/Navigation/PageTemplate/setStatus")
     * @ApiDescription("模板修改状态")
     * @Method(allow=["POST"])
     * @Param(name="pageTemplateZoneRelationId", alias="关联id", type="int", required="", min="1", description="关联id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $pageTemplateZoneRelation = PageTemplateZoneRelationModel::create()->get($param['pageTemplateZoneRelationId']);

            if (!$pageTemplateZoneRelation) {
                throw new Exception('无效的关联id', Status::CODE_BAD_REQUEST);
            }

            $result = $pageTemplateZoneRelation->update(['status' => intval($param['status'])]);

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
     * 模板修改排序
     * @Api(name="模板修改排序",path="/Api/Admin/Navigation/PageTemplate/setSort")
     * @ApiDescription("模板修改排序")
     * @Method(allow=["POST"])
     * @Param(name="pageTemplateZoneRelationId", alias="关联id", type="int", required="", min="1", description="关联id")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="255", description="排序")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function setSort()
    {
        $param = $this->request()->getRequestParam();

        try {
            $pageTemplateZoneRelation = PageTemplateZoneRelationModel::create()->get($param['pageTemplateZoneRelationId']);

            if (!$pageTemplateZoneRelation) {
                throw new Exception('无效的关联id', Status::CODE_BAD_REQUEST);
            }

            $result = $pageTemplateZoneRelation->update(['sort' => $param['sort']]);

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
     * 删除模板数据缓存
     * @Api(name="删除模板数据缓存",path="/Api/Admin/Navigation/PageTemplate/deleteCache")
     * @ApiDescription("删除模板数据缓存，传id为单个操作，不传为全部操作。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageTemplateId", alias="模板id", type="int", optional="", min="1", description="模板id")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function deleteCache()
    {
        $param = $this->request()->getRequestParam();

        try {
            if (isset($param['pageTemplateId'])) {
                $pageTemplateIdList = [$param['pageTemplateId']];
            } else {
                $pageTemplateIdList = PageTemplateModel::create()->column('PageTemplateId');
            }

            $result = PageService::getInstance()->deleteTemplateCache($pageTemplateIdList);

            $pageIdList = PageModel::create()->column('pageId');
            PageService::getInstance()->deleteConfigPageCache($pageIdList);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
}