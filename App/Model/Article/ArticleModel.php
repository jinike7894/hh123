<?php

namespace App\Model\Article;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * Class ArticleModel
 * @package App\Model\Article
 * @property $articleId int | 文章id
 * @property $title string | 标题
 * @property $summary string | 摘要
 * @property $fileType string | 文件类型
 * @property $cover string | 封面0
 * @property $cover1 string | 封面1
 * @property $cover2 string | 封面2
 * @property $content string | 内容
 * @property $readCount int | 虚假阅读数
 * @property $realReadCount int | 真实阅读数
 * @property $likeCount int | 虚假点赞数
 * @property $realLikeCount int | 真实点赞数
 * @property $sort int | 排序
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ArticleModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'art_article';

    protected $primaryKey = 'articleId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const GROUP_KEY_PORN_NEWS = 'PornNews'; // 性闻
    const GROUP_KEY_MUCKRAKING = 'Muckraking'; // 吃瓜爆料

    const GROUP_KEY_ALL = ['PornNews', 'Muckraking'];
    const GROUP_KEY_LIST = [
        ['key' => self::GROUP_KEY_PORN_NEWS, 'name' => '性闻'],
        ['key' => self::GROUP_KEY_MUCKRAKING, 'name' => '吃瓜爆料'],
    ];

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function articleTagRelation()
    {
        return $this->hasMany(ArticleTagRelationModel::class, function (QueryBuilder $query) {
            $atr = ArticleTagRelationModel::create()->getTableName();
            $query
                ->fields(['articleId', 't.tagId', 'tagName', 'sort', 'status'])
                ->join(TagModel::create()->getTableName() . ' AS t', "t.tagId={$atr}.tagId", 'LEFT')
                ->orderBy('sort', ' DESC');
            return $query;
        }, 'articleId', 'articleId');
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['articleId']) && $keyword['articleId']) {
            if (is_array($keyword['articleId'])) {
                $where['articleId'] = [$keyword['articleId'], 'IN'];
            } else {
                $where['articleId'] = $keyword['articleId'];
            }
        }

        isset($keyword['articleTypeId']) && $keyword['articleTypeId'] && $where['articleTypeId'] = $keyword['articleTypeId'];
        isset($keyword['articleGroupKey']) && $keyword['articleGroupKey'] && $where['articleGroupKey'] = $keyword['articleGroupKey'];

        // 虽然我很不想在左边加百分号，但是想着总共数据也不多，操作会更方便，还是加上的吧。
        # TODO: 后面改成全文搜索
        isset($keyword['title']) && $keyword['title'] && $where['title'] = ['%' . $keyword['title'] . '%', 'LIKE'];
        // isset($keyword['title']) && $keyword['title'] && $where['title'] = [$keyword['title'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    public function setOrderType($sortType)
    {
        if ($sortType) {
            $sortType = explode('_', $sortType);
            $this->order(...$sortType);

            if ($sortType[0] == 'sort') {
                // 如果选择的是按照sort排序，那么依然要将id DESC作为第二排序
                $this->setDefaultOrder();
            }
        } else {
            $this->setDefaultOrder();
        }

        return $this;
    }
}