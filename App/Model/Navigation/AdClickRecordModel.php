<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class AdClickRecordModel
 * @package App\Model\Navigation
 * @property $pageId int | 页面id
 * @property $date date | 日期
 * @property $deviceId string | 设备id
 * @property $ipLong int | ipLong值
 * @property $screen string | 屏幕宽高 360x740
 * @property $adId int | 广告id
 * @property $ip string | ipV4字符串
 * @property $clickCount int | 点击计数
 * @property $firstTime datetime | 第一次点击时间
 * @property $latestTime datetime | 最后一次点击时间
 */
class AdClickRecordModel extends BaseModel
{
    protected $tableName = 'nav_ad_click_record';

    protected $primaryKey = ['pageId', 'date', 'deviceId', 'ipLong', 'screen', 'adId'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];
        isset($keyword['pageId']) && $keyword['pageId'] && $where['pageId'] = $keyword['pageId'];
        isset($keyword['deviceId']) && $keyword['deviceId'] && $where['deviceId'] = $keyword['deviceId'];

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

        isset($keyword['screen']) && $keyword['screen'] && $where['screen'] = $keyword['screen'];
        isset($keyword['ipLong']) && $keyword['ipLong'] && $where['ipLong'] = $keyword['ipLong'];
        isset($keyword['clickCount']) && $keyword['clickCount'] && $where['clickCount'] = [$keyword['clickCount'] - 1, '>'];

        return $where;
    }
}