<?php

namespace App\HttpController\Api\Article;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\User\UserBase;
use App\Model\Article\ArticleModel;
use App\Model\Article\ArticleTagRelationModel;
use App\Model\Article\ArticleTypeModel;
use App\Model\Article\TagModel;
use App\Service\Article\ArticleService;
use App\Service\Article\TagService;
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
use EasySwoole\Mysqli\QueryBuilder;
use Exception;
use Throwable;

/**
 * Class Article
 * @package App\HttpController\Api\Article
 * @ApiGroup(groupName="文章 Article/Article")
 * @ApiGroupDescription("文章相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Article extends UserBase
{
    /**
     * 文章列表
     * @Api(name="文章列表",path="/Api/Article/Article/articleList")
     * @ApiDescription("文章列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="search", alias="搜索", type="string", optional="", mbLengthMin="1", description="搜索")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["sort_DESC", "readCount_DESC", "likeCount_DESC"], description="1.最新（sort_DESC）2.阅读最多（readCount_DESC）3.点赞最多（likeCount_DESC）")
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", optional="", mbLengthMin="1", description="文章分组key")
     * @Param(name="articleTypeId", alias="文章分类id", type="int", optional="", min="0", description="文章分类id")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"articleId":3,"title":"教兄弟的姐姐在嘴里含着跳跳糖给我口交，各种道具往逼里塞，最后再用大鸡吧好好使劲儿捅！","summary":"","fileType":"up","cover":"/Upload/Image/article/2023/10/26/17b701657c50b93ed41ac6b8fd6b6a93.png","cover1":"/Upload/Image/article/2023/10/26/a05825627115991412c2803b6ea37470.png","cover2":"/Upload/Image/article/2023/10/27/66802d5d8137e1a3c73d2a05a5fad27b.png","readCount":32456,"likeCount":13453,"sort":0,"createTime":"2023-10-26 13:47:31","articleTagRelation":[]},{"articleId":2,"title":"刺青妹子","summary":"","fileType":"awsS3","cover":"/Upload/Image/article/2023/10/21/27f1e763f882b990ab0c6f1c0bd2d68c.png","cover1":"/Upload/Image/article/2023/10/21/b1554f2d538769d7416fcef42fb315fe.png","cover2":"/Upload/Image/article/2023/10/21/94d9e17d0f4ba175a4b2d661216e88bb.png","readCount":189000,"likeCount":18000,"sort":0,"createTime":"2023-10-21 20:56:48","articleTagRelation":[]},{"articleId":1,"title":"母狗吃鸡","summary":"","fileType":"awsS3","cover":"/Upload/Image/article/2023/10/21/0b4384ad102b2f11ac5d648081333801.png","cover1":"/Upload/Image/article/2023/10/21/30879569379d0a3f69f41d56db56c666.png","cover2":"/Upload/Image/article/2023/10/21/fdde93978b1f21e131126b368342da4b.png","readCount":100000,"likeCount":15000,"sort":0,"createTime":"2023-10-21 20:50:38","articleTagRelation":[]}],"options":{"status":1}},"systemTimestamp":1699346672,"systemDateTime":"2023-11-07 16:44:32","msg":"OK"})
     */
    public function articleList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['articleTypeId']) && $keyword['articleTypeId'] = intval($param['articleTypeId']);
            isset($param['articleGroupKey']) && $keyword['articleGroupKey'] = $param['articleGroupKey'];
            $keyword['status'] = 1;

            $field = [
                'articleId',
                'title',
                'summary',
                'fileType',
                'cover',
                'cover1',
                'cover2',
                'readCount',
                'likeCount',
                'sort',
                'createTime',
            ];

            $sortType = $param['sortType'] ?? '';

            $article = ArticleModel::create();

            // 两个分支
            // 1.判断搜索词是否精准匹配tag，如果精准tag，则查询所有tag关联的数据
            // 2.如果没有精准tag，则模糊搜索文章标题和模糊搜索tag名
            if (isset($param['search'])) {
                $tag = TagModel::create()->get(['tagName' => $param['search'], 'status' => TagModel::STATE_NORMAL]);
                if ($tag) {
                    // 下面这一句就是有精准的tagId
                    // SELECT SQL_CALC_FOUND_ROWS a.articleId, title, summary, fileType, cover, cover1, cover2, readCount, likeCount, sort, status, createTime, updateTime FROM art_article AS `a` LEFT JOIN art_article_tag_relation AS atr on a.articleId = atr.articleId WHERE  `atr`.`tagId` = 1  AND `status` = 1  ORDER BY articleId DESC  LIMIT 0, 20
                    $field[0] = 'a.articleId';

                    $article
                        ->alias('a')
                        ->join(ArticleTagRelationModel::create()->getTableName() . ' AS atr', 'a.articleId = atr.articleId', 'LEFT')
                        ->where('atr.tagId', $tag->tagId);
                } else {
                    $articleIdList = TagService::getInstance()->searchRelatedArticleIdList($param['search']);

                    if ($articleIdList) {
                        // 这个很特么特殊
                        // SELECT SQL_CALC_FOUND_ROWS articleId, title, summary, fileType, cover, cover1, cover2, readCount, likeCount, sort, status, createTime, updateTime FROM `art_article` WHERE   (`articleId` IN (1) OR `title` LIKE '%反%')  AND `status` = 1  ORDER BY articleId DESC  LIMIT 0, 20
                        // 注意这里对文章标题还是用了2个百分号
                        $article->where(' (`articleId` IN (' . implode(',', $articleIdList) . ') OR `title` LIKE ?) ', ['%' . $param['search'] . '%']);
                    } else {
                        $keyword['title'] = $param['search'];
                    }
                }
            }

            $data = $article
                ->with(['articleTagRelation'])
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

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
     * @Api(name="文章详情",path="/Api/Article/Article/articleDetail")
     * @ApiDescription("文章详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @ApiSuccess({"code":200,"result":{"articleId":1,"title":"测试文章","summary":"摘要","fileType":"awsS3","cover":"/Upload/Image/article/2023/09/28/29e9da07f7c04b36a775aabff62392f4.gif","cover1":"","cover2":"","content":"<p>文章内容</p>","readCount":100,"likeCount":10,"sort":0,"status":1,"createTime":"2023-10-19 14:40:55","articleTagRelation":[{"articleId":1,"tagId":1,"tagName":"反差婊","sort":0,"status":1},{"articleId":1,"tagId":2,"tagName":"18禁情报","sort":0,"status":1}]},"systemTimestamp":1697791088,"systemDateTime":"2023-10-20 16:38:08","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"articleId":3,"title":"标题1234","summary":"摘要1234","fileType":"up","cover":"/Init/Zone/3/3_1.gif","cover1":"/Init/Zone/3/3_1.gif","cover2":"/Init/Zone/3/3_1.gif","content":"<p>内容<b>加粗</b></p>","readCount":100,"likeCount":15,"sort":0,"status":1,"createTime":"2023-10-19 20:42:27","updateTime":"2023-10-19 21:33:01","articleTagRelation":[]},"systemTimestamp":1697791257,"systemDateTime":"2023-10-20 16:40:57","msg":"OK"})
     */
    public function articleDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $article = ArticleModel::create()
                ->with(['articleTagRelation'])
                ->get([
                    'articleId' => $param['articleId'],
                    'status' => ArticleModel::STATE_NORMAL,
                ]);

            if (!$article) {
                throw new Exception('无效的文章id', Status::CODE_BAD_REQUEST);
            }

            // 增加阅读数
            ArticleService::getInstance()->read($this->who['userId'], $article->articleId);

            $article->hidden(['realReadCount', 'realLikeCount']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $article, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 文章点赞
     * @Api(name="文章点赞",path="/Api/Article/Article/like")
     * @ApiDescription("文章点赞，如果已经点赞过，再次请求则为取消点赞。点赞结果为1，取消点赞结果为0")
     * @Method(allow=["POST"])
     * @Param(name="articleId", alias="文章id", type="int", required="", min="1", description="文章id")
     * @ApiSuccess({"code":200,"result":1,"systemTimestamp":1698212049,"systemDateTime":"2023-10-25 13:34:09","msg":"OK"})
     * @ApiSuccess({"code":200,"result":0,"systemTimestamp":1698212049,"systemDateTime":"2023-10-25 13:34:09","msg":"OK"})
     */
    public function like()
    {
        $param = $this->request()->getRequestParam();

        try {
            $article = ArticleModel::create()
                ->get([
                    'articleId' => $param['articleId'],
                    'status' => ArticleModel::STATE_NORMAL,
                ]);

            if (!$article) {
                throw new Exception('无效的文章id', Status::CODE_BAD_REQUEST);
            }

            $isAlreadyLike = ArticleService::getInstance()->isAlreadyLike($this->who['userId'], $article->articleId);
            if ($isAlreadyLike) {
                ArticleService::getInstance()->dislike($this->who['userId'], $article->articleId);
                $result = 0;
            } else {
                ArticleService::getInstance()->like($this->who['userId'], $article->articleId);
                $result = 1;
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 获取文章分类列表
     * @Api(name="获取文章分类列表",path="/Api/Article/Article/getArticleTypeyList")
     * @ApiDescription("获取文章分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="articleGroupKey", alias="文章分组key", type="int", required="", mbLengthMin="1", description="文章分组key")
     * @ApiSuccess({"code":200,"result":[{"articleTypeId":1,"articleTypeName":"热点事件"},{"articleTypeId":2,"articleTypeName":"往期回顾"},{"articleTypeId":3,"articleTypeName":"性癖专场"},{"articleTypeId":4,"articleTypeName":"国产探花"}],"systemTimestamp":1699259731,"systemDateTime":"2023-11-06 16:35:31","msg":"OK"})
     */
    public function getArticleTypeyList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = ArticleTypeModel::create()
                ->field(['articleTypeId', 'articleTypeName'])
                ->where([
                    'articleGroupKey' => $param['articleGroupKey'],
                    'status' => ArticleTypeModel::STATE_NORMAL,
                ])
                ->order('sort', 'ASC')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}