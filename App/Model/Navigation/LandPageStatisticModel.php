<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class LandpagePageStatisticModel
 * @package App\Model\Navigation
 * @property $channelKey int | 渠道key
 * @property $date date | 日期
 * @property $ip int | ip统计
 */
class LandPageStatisticModel extends BaseModel
{
    protected $tableName = 'nav_landpage_statistic';

    protected $primaryKey = ['channelKey', 'date'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];
        isset($keyword['channelKey']) && $keyword['channelKey'] && $where['channelKey'] = is_array($keyword['channelKey']) ? [$keyword['channelKey'], 'IN'] : $keyword['channelKey'];


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
            $this->order('date', 'DESC');
        }

        return $this;
    }
    public function getSum($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);

        return $this
            ->field([
                'IFNULL(SUM(ip),0) AS ip',
                'IFNULL(SUM(dh),0) AS dh',
                'IFNULL(SUM(welfare),0) AS welfare',
            ])
            ->where($where)
            ->get();
    }
}