<?php

namespace App\HttpController\Api\Admin\Prostitute;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Prostitute\ProstituteTypeModel;
use App\Service\Prostitute\ProstituteTypeService;
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
 * Class ProstituteType
 * @package App\HttpController\Api\Admin\Prostitute
 * @ApiGroup(groupName="后台-楼凤-楼凤类型 Admin/Prostitute/ProstituteType")
 * @ApiGroupDescription("后台楼凤模块楼凤类型相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class ProstituteType extends AdminBase
{
    /**
     * 楼凤分类全列表
     * @Api(name="楼凤分类全列表",path="/Api/Admin/Prostitute/ProstituteType/prostituteTypeAllList")
     * @ApiDescription("楼凤分类全列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"prostituteTypeId":1,"title":"楼凤信息","typeKey":"Information","prostituteTypeExtensionRelation":[{"prostituteTypeExtId":1,"prostituteTypeId":1,"fieldKey":"number","fieldName":"妹子数量"},{"prostituteTypeExtId":2,"prostituteTypeId":1,"fieldKey":"age","fieldName":"妹子年龄"},{"prostituteTypeExtId":3,"prostituteTypeId":1,"fieldKey":"service","fieldName":"服务项目"},{"prostituteTypeExtId":4,"prostituteTypeId":1,"fieldKey":"duration","fieldName":"服务时间"},{"prostituteTypeExtId":5,"prostituteTypeId":1,"fieldKey":"cost","fieldName":"服务费用"},{"prostituteTypeExtId":6,"prostituteTypeId":1,"fieldKey":"avatar","fieldName":"头像"},{"prostituteTypeExtId":7,"prostituteTypeId":1,"fieldKey":"fileType","fieldName":"url"},{"prostituteTypeExtId":8,"prostituteTypeId":1,"fieldKey":"author","fieldName":"作者"}]},{"prostituteTypeId":2,"title":"认证外围","typeKey":"Certified","prostituteTypeExtensionRelation":[{"prostituteTypeExtId":9,"prostituteTypeId":2,"fieldKey":"costP","fieldName":"一次费用"},{"prostituteTypeExtId":10,"prostituteTypeId":2,"fieldKey":"cost2P","fieldName":"两次费用"},{"prostituteTypeExtId":11,"prostituteTypeId":2,"fieldKey":"costN","fieldName":"包夜费用"},{"prostituteTypeExtId":12,"prostituteTypeId":2,"fieldKey":"service","fieldName":"服务项目"},{"prostituteTypeExtId":13,"prostituteTypeId":2,"fieldKey":"age","fieldName":"年龄"},{"prostituteTypeExtId":14,"prostituteTypeId":2,"fieldKey":"height","fieldName":"身高"}]},{"prostituteTypeId":3,"title":"包养入住","typeKey":"Kept","prostituteTypeExtensionRelation":[{"prostituteTypeExtId":15,"prostituteTypeId":3,"fieldKey":"gender","fieldName":"性别"},{"prostituteTypeExtId":16,"prostituteTypeId":3,"fieldKey":"cost","fieldName":"费用"},{"prostituteTypeExtId":17,"prostituteTypeId":3,"fieldKey":"age","fieldName":"年龄"},{"prostituteTypeExtId":18,"prostituteTypeId":3,"fieldKey":"height","fieldName":"身高"}]}],"systemTimestamp":1700286116,"systemDateTime":"2023-11-18 13:41:56","msg":"OK"})
     */
    public function prostituteTypeAllList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            $field = [
                'prostituteTypeId',
                'title',
                'typeKey',
            ];

            $data = ProstituteTypeModel::create()
                ->with('prostituteTypeExtensionRelation')
                ->field($field)
                ->setKeyWord($keyword)
                ->order('sort', 'DESC')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤分类列表
     * @Api(name="楼凤分类列表",path="/Api/Admin/Prostitute/ProstituteType/prostituteTypeList")
     * @ApiDescription("楼凤分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @Param(name="title", alias="标题", type="string", optional="", mbLengthMin="1", description="标题")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["prostituteTypeId_DESC", "prostituteTypeId_ASC", "sort_ASC", "sort_DESC"], description="1.分类id（prostituteTypeId）2.排序值（sort）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"prostituteTypeId":1,"title":"楼凤信息","typeKey":"Information","relatedAdId":"1","sort":30,"status":1},{"prostituteTypeId":2,"title":"认证外围","typeKey":"Certified","relatedAdId":"2","sort":20,"status":1},{"prostituteTypeId":3,"title":"包养入住","typeKey":"Kept","relatedAdId":"3","sort":10,"status":1}],"options":[]},"systemTimestamp":1700555824,"systemDateTime":"2023-11-21 16:37:04","msg":"OK"})
     */
    public function prostituteTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['prostituteTypeId']) && $keyword['prostituteTypeId'] = intval($param['prostituteTypeId']);
            isset($param['title']) && $keyword['title'] = trim($param['title']);
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'prostituteTypeId',
                'title',
                'typeKey',
                'relatedAdId',
                'sort',
                'status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ProstituteTypeModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤分类详情
     * @Api(name="楼凤分类详情",path="/Api/Admin/Prostitute/ProstituteType/articleTypeDetail")
     * @ApiDescription("楼凤分类详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @ApiSuccess({"code":200,"result":{"prostituteTypeId":1,"title":"楼凤信息","typeKey":"Information","relatedAdId":"1","sort":30,"status":1,"createTime":"2023-11-17 16:20:34","updateTime":"2023-11-17 19:32:00"},"systemTimestamp":1700556019,"systemDateTime":"2023-11-21 16:40:19","msg":"OK"})
     */
    public function articleTypeDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $prostituteType = ProstituteTypeModel::create()
                ->get([
                    'prostituteTypeId' => $param['prostituteTypeId'],
                    'status' => [ProstituteTypeModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $prostituteType, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤类型编辑
     * @Api(name="楼凤类型编辑",path="/Api/Admin/Prostitute/ProstituteType/edit")
     * @ApiDescription("楼凤类型编辑")
     * @Method(allow=["POST"])
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @Param(name="title", alias="标题", type="string", optional="", mbLengthMin="1", description="标题")
     * @Param(name="relatedAdId", alias="关联的广告id", type="string", optional="", description="关联的广告id，多个逗号分割")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1700556422,"systemDateTime":"2023-11-21 16:47:02","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteTypeId' => intval($param['prostituteTypeId']),
                'title' => trim($param['title']),
                'relatedAdId' => trim($param['relatedAdId']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = ProstituteTypeService::getInstance()->editProstituteType($data);

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
     * 楼凤分类修改状态
     * @Api(name="楼凤分类修改状态",path="/Api/Admin/Prostitute/ProstituteType/setStatus")
     * @ApiDescription("楼凤分类修改状态")
     * @Method(allow=["POST"])
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteTypeId' => intval($param['prostituteTypeId']),
                'status' => intval($param['status']),
            ];

            $result = ProstituteTypeService::getInstance()->editProstituteType($data);

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