<?php

namespace App\HttpController\Api\Admin\Article;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Article\ArticleTypeModel;
use App\Service\Article\ArticleTypeService;
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
use Exception;
use Throwable;

/**
 * Class ArticleType
 * @package App\HttpController\Api\Admin\Article
 * @ApiGroup(groupName="后台-文章-文章分类 Admin/Article/ArticleType")
 * @ApiGroupDescription("后台文章模块文章分类相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class ArticleType extends AdminBase
{
    /**
     * 文章分类列表
     * @Api(name="文章分类列表",path="/Api/Admin/Article/ArticleType/articleTypeList")
     * @ApiDescription("文章分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", optional="", mbLengthMin="1", description="文章分组key")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["articleTypeId_DESC", "articleTypeId_ASC", "sort_ASC", "sort_DESC"], description="1.类型id（articleTypeId）2.排序值（sort）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":7,"list":[{"articleTypeId":7,"articleTypeParentId":5,"articleGroupKey":"Muckraking","articleTypeName":"下下级1","sort":0,"status":1,"articleTypeParnetName":"下级1"},{"articleTypeId":6,"articleTypeParentId":1,"articleGroupKey":"Muckraking","articleTypeName":"下级2","sort":0,"status":1,"articleTypeParnetName":"热点事件"},{"articleTypeId":5,"articleTypeParentId":1,"articleGroupKey":"Muckraking","articleTypeName":"下级1","sort":0,"status":1,"articleTypeParnetName":"热点事件"},{"articleTypeId":4,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"国产探花","sort":40,"status":1,"articleTypeParnetName":""},{"articleTypeId":3,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"性癖专场","sort":30,"status":1,"articleTypeParnetName":""},{"articleTypeId":2,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"往期回顾","sort":20,"status":1,"articleTypeParnetName":""},{"articleTypeId":1,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"热点事件","sort":10,"status":1,"articleTypeParnetName":""}],"options":[]},"systemTimestamp":1699346498,"systemDateTime":"2023-11-07 16:41:38","msg":"OK"})
     */
    public function articleTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['status']) && $keyword['at.status'] = intval($param['status']);
            isset($param['articleGroupKey']) && $keyword['at.articleGroupKey'] = $param['articleGroupKey'];

            $field = [
                'at.articleTypeId',
                'at.articleTypeParentId',
                'at.articleGroupKey',
                'at.articleTypeName',
                'IFNULL(at2.articleTypeName,"") AS articleTypeParnetName',
                'at.sort',
                'at.status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ArticleTypeModel::create()
                ->alias('at')
                ->join(ArticleTypeModel::create()->getTableName() . ' AS at2', 'at.articleTypeParentId = at2.articleTypeId', 'LEFT')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章分类列表（树）
     * @Api(name="文章分类列表（树）",path="/Api/Admin/Article/ArticleType/articleTypeListTree")
     * @ApiDescription("文章分类列表（树）")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", optional="", mbLengthMin="1", description="文章分组key")
     * @ApiSuccess({"code":200,"result":[{"articleTypeId":1,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"热点事件","sort":10,"status":1,"children":[{"articleTypeId":5,"articleTypeParentId":1,"articleGroupKey":"Muckraking","articleTypeName":"下级1","sort":0,"status":1,"children":[{"articleTypeId":7,"articleTypeParentId":5,"articleGroupKey":"Muckraking","articleTypeName":"下下级1","sort":0,"status":1}]},{"articleTypeId":6,"articleTypeParentId":1,"articleGroupKey":"Muckraking","articleTypeName":"下级2","sort":0,"status":1}]},{"articleTypeId":2,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"往期回顾","sort":20,"status":1},{"articleTypeId":3,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"性癖专场","sort":30,"status":1},{"articleTypeId":4,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"国产探花","sort":40,"status":1}],"systemTimestamp":1699346471,"systemDateTime":"2023-11-07 16:41:11","msg":"OK"})
     */
    public function articleTypeListTree()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            isset($param['articleGroupKey']) && $keyword['articleGroupKey'] = $param['articleGroupKey'];

            $field = [
                'articleTypeId',
                'articleTypeParentId',
                'articleGroupKey',
                'articleTypeName',
                'sort',
                'status',
            ];

            $articleType = ArticleTypeModel::create();
            $where = $articleType->parseKeywordToWhere($keyword);
            $data = $articleType->field($field)->where($where)->indexBy('articleTypeId');
            $data = Func::treeArray($data, 'articleTypeId', 'articleTypeParentId');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章分类详情
     * @Api(name="文章分类详情",path="/Api/Admin/Article/ArticleType/articleTypeDetail")
     * @ApiDescription("文章分类详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="1", description="文章分类id")
     * @ApiSuccess({"code":200,"result":{"articleTypeId":1,"articleTypeParentId":0,"articleGroupKey":"Muckraking","articleTypeName":"热点事件","sort":10,"status":1,"createTime":"2023-11-06 13:27:36","updateTime":"2023-11-06 13:27:36"},"systemTimestamp":1699278729,"systemDateTime":"2023-11-06 21:52:09","msg":"OK"})
     */
    public function articleTypeDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $articleType = ArticleTypeModel::create()
                ->get([
                    'articleTypeId' => $param['articleTypeId'],
                    'status' => [ArticleTypeModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $articleType, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章类型添加
     * @Api(name="文章类型添加",path="/Api/Admin/Article/ArticleType/add")
     * @ApiDescription("文章类型添加")
     * @Method(allow=["POST"])
     * @Param(name="articleTypeParentId", alias="文章分类父级id", type="int", required="", min="0", description="文章分类父级id，选顶级则传0")
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", required="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeName", alias="文章分类名", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="文章分类名")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleTypeParentId' => intval($param['articleTypeParentId']),
                'articleGroupKey' => trim($param['articleGroupKey']),
                'articleTypeName' => trim($param['articleTypeName']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = ArticleTypeService::getInstance()->addArticleType($data);

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
     * 文章类型编辑
     * @Api(name="文章类型编辑",path="/Api/Admin/Article/ArticleType/edit")
     * @ApiDescription("文章类型编辑")
     * @Method(allow=["POST"])
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="1", description="文章分类id")
     * @Param(name="articleTypeParentId", alias="文章分类父级id", type="int", required="", min="0", description="文章分类父级id，选顶级则传0")
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", required="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeName", alias="文章分类名", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="文章分类名")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleTypeId' => intval($param['articleTypeId']),
                'articleTypeParentId' => intval($param['articleTypeParentId']),
                'articleGroupKey' => trim($param['articleGroupKey']),
                'articleTypeName' => trim($param['articleTypeName']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = ArticleTypeService::getInstance()->editArticleType($data);

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
     * 文章分类修改状态
     * @Api(name="文章分类修改状态",path="/Api/Admin/Article/ArticleType/setStatus")
     * @ApiDescription("文章分类修改状态")
     * @Method(allow=["POST"])
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="1", description="文章分类id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleTypeId' => $param['articleTypeId'],
                'status' => intval($param['status']),
            ];

            $result = ArticleTypeService::getInstance()->editArticleType($data);

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
     * 文章分类删除
     * @Api(name="文章分类删除",path="/Api/Admin/Article/ArticleType/delete")
     * @ApiDescription("文章分类删除")
     * @Method(allow=["POST"])
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="1", description="文章分类id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleTypeId' => $param['articleTypeId'],
                'status' => ArticleTypeModel::STATE_DELETED,
            ];

            $result = ArticleTypeService::getInstance()->editArticleType($data);

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