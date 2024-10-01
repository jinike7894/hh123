<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class AdClickStatisticModel
 * @package App\Model\Navigation
 * @property $pageId int | 页面id
 * @property $date date | 日期
 * @property $adId int | 广告id
 * @property $clickCount int | 点击计数
 * @property $clickIpCount int | 点击ip计数
 * @property $totalCost float | 费用总计
 */
class AdClickStatisticModel extends BaseModel
{
    protected $tableName = 'nav_ad_click_statistic';

    protected $primaryKey = ['pageId', 'date', 'adId'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if(isset($keyword['pageId']) && $keyword['pageId'] ){
            $where['pageId'] = is_array($keyword['pageId']) ? [$keyword['pageId'], 'IN'] : $keyword['pageId'];
        }

        if (isset($keyword['adId']) && $keyword['adId']) {
            $where['adId'] = is_array($keyword['adId']) ? [$keyword['adId'], 'IN'] : $keyword['adId'];
        }

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['dateStart'])) {
            $this->where('date', $keyword['dateStart'], '>=');
        }
        if (isset($keyword['dateEnd'])) {
            $this->where('date', $keyword['dateEnd'], '<=');
        }

        if (isset($keyword['acs.dateStart'])) {
            $this->where('acs.date', $keyword['acs.dateStart'], '>=');
        }
        if (isset($keyword['acs.dateEnd'])) {
            $this->where('acs.date', $keyword['acs.dateEnd'], '<=');
        }

        return $where;
    }

    public function setOrderType($sortType)
    {
        if ($sortType) {
            $sortType = explode('_', $sortType);
            $this->order(...$sortType);
        } else {
            $this
                ->order('clickCount', 'DESC')
                ->order('totalCost', 'DESC')
                ->order('adId', 'DESC');
        }

        return $this;
    }

    /**
     * 获取范围内统计
     * @param $keyword
     */
    public function getSum($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);

        return $this
            ->field([
                'IFNULL(SUM(clickCount),0) AS clickCount',
                'IFNULL(SUM(retainedClickCount),0) AS retainedClickCount',
                'IFNULL(SUM(clickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newClickCount',
                'IFNULL(SUM(h5ClickCount),0) AS h5ClickCount',
                'IFNULL(SUM(appClickCount),0) AS appClickCount',
                'IFNULL(SUM(appClickCount),0) - IFNULL(SUM(retainedClickCount),0) AS newAppClickCount',
                'IFNULL(SUM(totalCost),0) AS totalCost',
            ])
            ->where($where)
            ->get();
    }
}