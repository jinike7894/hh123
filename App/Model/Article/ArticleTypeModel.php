<?php

namespace App\Model\Article;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class ArticleTypeModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'art_article_type';

    protected $primaryKey = 'articleTypeId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['articleTypeId']) && $keyword['articleTypeId'] && $where['articleTypeId'] = $keyword['articleTypeId'];
        isset($keyword['articleGroupKey']) && $keyword['articleGroupKey'] && $where['articleGroupKey'] = $keyword['articleGroupKey'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    public function getAll($page = 1, $keyword = [], $pageSize = 20, $field = ['*'])
    {

        $list = $this
            ->limit($pageSize * ($page - 1), $pageSize)
            ->withTotalCount()
            ->field($field)
            ->where($keyword)
            ->all();

        $total = $this->lastQueryResult()->getTotalCount();

        // return ['total' => $total, 'list' => $list, 'options' => $keyword, 'query' => $this->lastQuery()->getLastQuery()];
        return ['total' => $total, 'list' => $list, 'options' => $keyword];
    }
}