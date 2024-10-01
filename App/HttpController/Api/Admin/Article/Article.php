<?php

namespace App\HttpController\Api\Admin\Article;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Article\ArticleModel;
use App\Model\Article\ArticleTypeModel;
use App\Service\Article\ArticleService;
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
 * Class Article
 * @package App\HttpController\Api\Admin\Article
 * @ApiGroup(groupName="后台-文章-文章 Admin/Article/Article")
 * @ApiGroupDescription("后台文章模块文章相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Article extends AdminBase
{
    /**
     * 文章列表
     * @Api(name="文章列表",path="/Api/Admin/Article/Article/articleList")
     * @ApiDescription("文章列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="articleId", alias="文章id", type="int", optional="", min="1", description="文章id")
     * @Param(name="title", alias="标题", type="string", optional="", mbLengthMin="1", description="标题")
     * @Param(name="articleGroupKey", alias="文章分组key", type="string", optional="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeId", alias="文章分类id", type="int", optional="", min="0", description="文章分类id")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["articleId_DESC", "articleId_ASC", "sort_DESC", "sort_ASC"], description="1.id倒叙（articleId_DESC）2.id正叙（articleId_ASC）3.sort倒叙（sort_DESC）4.sort正叙（sort_ASC）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"articleId":3,"title":"标题1234","cover":"/Init/Zone/3/3_1.gif","cover1":"/Init/Zone/3/3_1.gif","cover2":"/Init/Zone/3/3_1.gif","realReadCount":0,"realLikeCount":0,"sort":0,"status":1,"createTime":"2023-10-19 20:42:27","updateTime":"2023-10-19 21:33:01","articleTagRelation":[]},{"articleId":2,"title":"测试文章2","cover":"","cover1":"","cover2":"","realReadCount":0,"realLikeCount":0,"sort":0,"status":1,"createTime":"1000-01-01 00:00:00","updateTime":"2023-10-19 16:01:09","articleTagRelation":[{"articleId":2,"tagId":1,"tagName":"反差婊","sort":0,"status":1}]},{"articleId":1,"title":"测试文章","cover":"","cover1":"","cover2":"","realReadCount":0,"realLikeCount":0,"sort":0,"status":1,"createTime":"2023-10-19 14:40:55","updateTime":"2023-10-19 14:51:11","articleTagRelation":[{"articleId":1,"tagId":1,"tagName":"反差婊","sort":0,"status":1},{"articleId":1,"tagId":2,"tagName":"18禁情报","sort":0,"status":1}]}],"options":[]},"systemTimestamp":1697722953,"systemDateTime":"2023-10-19 21:42:33","msg":"OK"})
     */
    public function articleList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['articleId']) && $keyword['articleId'] = $param['articleId'];
            isset($param['title']) && $keyword['title'] = $param['title'];
            isset($param['status']) && $keyword['status'] = intval($param['status']);
            isset($param['articleTypeId']) && $keyword['articleTypeId'] = intval($param['articleTypeId']);
            isset($param['articleGroupKey']) && $keyword['articleGroupKey'] = $param['articleGroupKey'];

            $field = [
                'articleId',
                'articleTypeId',
                'articleGroupKey',
                'title',
                'fileType',
                'cover',
                'realReadCount',
                'realLikeCount',
                'sort',
                'status',
                'createTime',
                'updateTime',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ArticleModel::create()
                ->with(['articleTagRelation'])
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = ArticleTypeModel::create()->appendInfo($data['list'], ['articleTypeName'], 'articleTypeId', 'articleTypeId');

            foreach ($data['list'] as &$item) {
                if (!isset($item['articleTagRelation']) || !$item['articleTagRelation']) {
                    $item['articleTagRelation'] = [];
                }
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章详情
     * @Api(name="文章详情",path="/Api/Admin/Article/Article/articleDetail")
     * @ApiDescription("文章详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @ApiSuccess({"code":200,"result":{"articleId":1,"title":"测试文章","summary":"摘要","cover":"","cover1":"","cover2":"","content":"<p>文章内容</p>","readCount":100,"realReadCount":0,"likeCount":10,"realLikeCount":0,"sort":0,"status":1,"createTime":"2023-10-19 14:40:55","updateTime":"2023-10-19 14:51:11","articleTagRelation":[{"articleId":1,"tagId":1,"tagName":"反差婊","sort":0,"status":1},{"articleId":1,"tagId":2,"tagName":"18禁情报","sort":0,"status":1}]},"systemTimestamp":1697703253,"systemDateTime":"2023-10-19 16:14:13","msg":"OK"})
     */
    public function articleDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $article = ArticleModel::create()
                ->with(['articleTagRelation'])
                ->get([
                    'articleId' => $param['articleId'],
                    'status' => [ArticleModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $article, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章添加
     * @Api(name="文章添加",path="/Api/Admin/Article/Article/add")
     * @ApiDescription("文章添加")
     * @Method(allow=["POST"])
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", required="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="0", description="文章分类id")
     * @Param(name="title", alias="标题", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="标题")
     * @Param(name="summary", alias="摘要", type="string", required="", mbLengthMin="0", mbLengthMax="128", description="摘要")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="cover", alias="封面图0", type="string", required="", mbLengthMin="0", description="封面图0")
     * @Param(name="cover1", alias="封面图1", type="string", required="", mbLengthMin="0", description="封面图1")
     * @Param(name="cover2", alias="封面图2", type="string", required="", mbLengthMin="0", description="封面图2")
     * @Param(name="content", alias="内容", type="string", required="", mbLengthMin="0", description="内容")
     * @Param(name="readCount", alias="虚假阅读数", type="int", required="", min="0", description="虚假阅读数")
     * @Param(name="likeCount", alias="虚假点赞数", type="int", required="", min="0", description="虚假点赞数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @Param(name="tagList", alias="标签列表", type="string", required="", mbLengthMin="0", description="标签列表，多个用逗号分割")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692698884,"systemDateTime":"2023-08-22 18:08:04","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleGroupKey' => trim($param['articleGroupKey']),
                'articleTypeId' => intval($param['articleTypeId']),
                'title' => trim($param['title']),
                'summary' => trim($param['summary']),
                'fileType' => trim($param['fileType']),
                'cover' => trim($param['cover']),
                'cover1' => trim($param['cover1']),
                'cover2' => trim($param['cover2']),
                'content' => trim($param['content']),
                'readCount' => intval($param['readCount']),
                'likeCount' => intval($param['likeCount']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'tagList' => trim($param['tagList']),
            ];

            $result = ArticleService::getInstance()->addArticle($data);

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
     * 文章编辑
     * @Api(name="文章编辑",path="/Api/Admin/Article/Article/edit")
     * @ApiDescription("文章编辑")
     * @Method(allow=["POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", required="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeId", alias="文章分类id", type="int", required="", min="0", description="文章分类id")
     * @Param(name="title", alias="标题", type="string", required="", mbLengthMin="1", mbLengthMax="50", description="标题")
     * @Param(name="summary", alias="摘要", type="string", required="", mbLengthMin="0", mbLengthMax="128", description="摘要")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="cover", alias="封面图0", type="string", required="", mbLengthMin="0", description="封面图0")
     * @Param(name="cover1", alias="封面图1", type="string", required="", mbLengthMin="0", description="封面图1")
     * @Param(name="cover2", alias="封面图2", type="string", required="", mbLengthMin="0", description="封面图2")
     * @Param(name="content", alias="内容", type="string", required="", mbLengthMin="0", description="内容")
     * @Param(name="readCount", alias="虚假阅读数", type="int", required="", min="0", description="虚假阅读数")
     * @Param(name="likeCount", alias="虚假点赞数", type="int", required="", min="0", description="虚假点赞数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @Param(name="tagList", alias="标签列表", type="string", required="", mbLengthMin="0", description="标签列表，多个用逗号分割")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleId' => intval($param['articleId']),
                'articleGroupKey' => trim($param['articleGroupKey']),
                'articleTypeId' => intval($param['articleTypeId']),
                'title' => trim($param['title']),
                'summary' => trim($param['summary']),
                'fileType' => trim($param['fileType']),
                'cover' => trim($param['cover']),
                'cover1' => trim($param['cover1']),
                'cover2' => trim($param['cover2']),
                'content' => trim($param['content']),
                'readCount' => intval($param['readCount']),
                'likeCount' => intval($param['likeCount']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'tagList' => trim($param['tagList']),
            ];

            $result = ArticleService::getInstance()->editArticle($data);

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
     * 文章修改状态
     * @Api(name="文章修改状态",path="/Api/Admin/Article/Article/setStatus")
     * @ApiDescription("文章修改状态")
     * @Method(allow=["POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleId' => $param['articleId'],
                'status' => intval($param['status']),
            ];

            $result = ArticleService::getInstance()->editArticle($data, false);

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
     * 文章删除
     * @Api(name="文章删除",path="/Api/Admin/Article/Article/delete")
     * @ApiDescription("文章删除")
     * @Method(allow=["POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'articleId' => $param['articleId'],
                'status' => ArticleModel::STATE_DELETED,
            ];

            // 会同步删除文章和tag的关联键
            $result = ArticleService::getInstance()->editArticle($data);

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

    /**
     * 获取文章分组键列表
     * @Api(name="获取文章分组键列表",path="/Api/Admin/Article/Article/getArticleGroupKeyList")
     * @ApiDescription("获取文章分组键列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"key":"PornNews","name":"性闻"},{"key":"Muckraking","name":"吃瓜爆料"}],"systemTimestamp":1699256096,"systemDateTime":"2023-11-06 15:34:56","msg":"OK"})
     */
    public function getArticleGroupKeyList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = ArticleModel::GROUP_KEY_LIST;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 获取文章分类列表
     * @Api(name="获取文章分类列表",path="/Api/Admin/Article/Article/getArticleTypeyList")
     * @ApiDescription("获取文章分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", optional="", mbLengthMin="1", description="文章分组key")
     * @ApiSuccess({"code":200,"result":[{"articleTypeId":1,"articleTypeName":"热点事件"},{"articleTypeId":2,"articleTypeName":"往期回顾"},{"articleTypeId":3,"articleTypeName":"性癖专场"},{"articleTypeId":4,"articleTypeName":"国产探花"}],"systemTimestamp":1699259731,"systemDateTime":"2023-11-06 16:35:31","msg":"OK"})
     */
    public function getArticleTypeyList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            isset($param['articleGroupKey']) && $keyword['articleGroupKey'] = $param['articleGroupKey'];

            $articleType = ArticleTypeModel::create();
            $where = $articleType->parseKeywordToWhere($keyword);

            $data = $articleType
                ->field(['articleTypeId', 'articleTypeParentId', 'articleTypeName'])
                ->where($where)
                ->order('sort', 'ASC')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}