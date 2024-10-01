<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class PageStatisticModel
 * @package App\Model\Navigation
 * @property $pageId int | 页面id
 * @property $date date | 日期
 * @property $pv int | pv统计
 * @property $ip int | ip统计
 * @property $reducedIp int | 扣量后ip统计
 */
class PageStatisticModel extends BaseModel
{
    protected $tableName = 'nav_page_statistic';

    protected $primaryKey = ['pageId', 'date'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];
        isset($keyword['pageId']) && $keyword['pageId'] && $where['pageId'] = is_array($keyword['pageId']) ? [$keyword['pageId'], 'IN'] : $keyword['pageId'];
        isset($keyword['ps.pageId']) && $keyword['ps.pageId'] && $where['ps.pageId'] = is_array($keyword['ps.pageId']) ? [$keyword['ps.pageId'], 'IN'] : $keyword['ps.pageId'];

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['ps.dateStart'])) {
            $this->where('ps.date', $keyword['ps.dateStart'], '>=');
        }
        if (isset($keyword['ps.dateEnd'])) {
            $this->where('ps.date', $keyword['ps.dateEnd'], '<=');
        }

        if (isset($keyword['dateStart'])) {
            $this->where('date', $keyword['dateStart'], '>=');
        }
        if (isset($keyword['dateEnd'])) {
            $this->where('date', $keyword['dateEnd'], '<=');
        }

        return $where;
    }

    public function setOrderType($sortType)
    {
        if ($sortType) {
            $sortType = explode('_', $sortType);
            $this->order(...$sortType);
        } else {
            $this->order('pageId', 'DESC');
        }

        return $this;
    }

    public function getSum($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);

        return $this
            ->field([
                'IFNULL(SUM(ip),0) AS ip',
                'IFNULL(SUM(reducedIp),0) AS reducedIp'
            ])
            ->where($where)
            ->get();
    }
}