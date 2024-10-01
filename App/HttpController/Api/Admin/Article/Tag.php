<?php

namespace App\HttpController\Api\Admin\Article;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Article\TagModel;
use App\Service\Article\TagService;
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
 * Class Tag
 * @package App\HttpController\Api\Admin\Article
 * @ApiGroup(groupName="后台-文章-标签 Admin/Article/Tag")
 * @ApiGroupDescription("后台文章模块标签相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Tag extends AdminBase
{
    /**
     * 标签列表
     * @Api(name="标签列表",path="/Api/Admin/Article/Tag/tagList")
     * @ApiDescription("标签列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="tagName", alias="标签名", type="string", optional="", mbLengthMin="1", description="标签名")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["tagId_DESC", "tagId_ASC", "sort_ASC", "sort_DESC"], description="1.id倒叙（tagId_DESC）2.id正叙（tagId_ASC）3.sort正叙（sort_ASC） 4.sort倒叙（sort_DESC）")
     * @ApiSuccess()
     */
    public function tagList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['tagName']) && $keyword['tagName'] = $param['tagName'];

            $field = [
                'tagId',
                'tagName',
                'sort',
                'status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = TagModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 标签添加
     * @Api(name="标签添加",path="/Api/Admin/Article/Tag/add")
     * @ApiDescription("标签添加")
     * @Method(allow=["POST"])
     * @Param(name="tagName", alias="标签名", type="string", required="", mbLengthMin="1", description="标签名")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'tagName' => $param['tagName'],
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = TagService::getInstance()->addTag($data);

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
     * 标签编辑
     * @Api(name="标签编辑",path="/Api/Admin/Article/Tag/edit")
     * @ApiDescription("标签编辑")
     * @Method(allow=["POST"])
     * @Param(name="tagId", alias="标签id", type="int", required="", min="1", description="标签id")
     * @Param(name="tagName", alias="标签名", type="string", required="", mbLengthMin="1", description="标签名")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'tagId' => $param['tagId'],
                'tagName' => $param['tagName'],
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = TagService::getInstance()->editTag($data);

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
     * 标签修改状态
     * @Api(name="标签修改状态",path="/Api/Admin/Article/Tag/setStatus")
     * @ApiDescription("标签修改状态")
     * @Method(allow=["POST"])
     * @Param(name="tagId", alias="标签id", type="int", required="", min="1", description="标签id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'tagId' => $param['tagId'],
                'status' => intval($param['status']),
            ];

            $result = TagService::getInstance()->editTag($data);

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
     * 标签删除
     * @Api(name="标签删除",path="/Api/Admin/Article/Tag/delete")
     * @ApiDescription("标签删除")
     * @Method(allow=["POST"])
     * @Param(name="tagId", alias="标签id", type="int", required="", min="1", description="标签id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'tagId' => $param['tagId'],
            ];

            $result = TagService::getInstance()->deleteTag($data);

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