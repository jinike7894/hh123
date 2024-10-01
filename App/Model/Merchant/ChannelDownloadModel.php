<?php

namespace App\Model\Merchant;

use App\Model\BaseModel;

/**
 * Class ChannelInstallStatisticModel
 * @package App\Model\Merchant
 * @property $date date | 日期
 * @property $channelId int | 渠道id
 * @property $downClick int | 下载按钮点击数
 */
class ChannelDownloadModel extends BaseModel
{
    protected $tableName = 'ch_channel_download';

    protected $primaryKey = ['date', 'channelId'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['channelId'])) {
            if (is_array($keyword['channelId'])) {
                $where['channelId'] = [$keyword['channelId'], 'IN'];
            } else {
                $where['channelId'] = $keyword['channelId'];
            }
        }

        if (isset($keyword['c.channelId'])) {
            if (is_array($keyword['c.channelId'])) {
                $where['c.channelId'] = [$keyword['c.channelId'], 'IN'];
            } else {
                $where['c.channelId'] = $keyword['c.channelId'];
            }
        }

        isset($keyword['date']) && $keyword['date'] && $where['date'] = $keyword['date'];

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['dateStart'])) {
            $this->where('date', $keyword['dateStart'], '>=');
        }
        if (isset($keyword['dateEnd'])) {
            $this->where('date', $keyword['dateEnd'], '<=');
        }

        // 这里的只限制安卓的扣量安装数
        if (isset($keyword['countLimit'])) {
            $this->where('installAndroid', $keyword['countLimit'], '>=');
        }

        return $where;
    }

    public function setOrderType($sortType){
        if ($sortType) {
            $sortType = explode('_', $sortType);
            $this->order(...$sortType);
        } else {
            $this->order('date', 'DESC');
        }

        return $this;
    }

    /**
     * 按照日期统计
     * @param $date
     * @param int $merchantId
     * @return ChannelInstallStatisticModel|array|bool|\EasySwoole\ORM\AbstractModel|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    /*public function getTotalByDate($date, $merchantId = 0)
    {
        if ($merchantId) {
            $channelIdList = ChannelModel::create()
                ->where(['merchantId' => $merchantId, 'status' => ChannelModel::STATE_NORMAL])
                ->column('channelId');

            $channelIdList && $this->where(['channelId' => [$channelIdList, 'IN']]);
        }

        return $this
            ->field([
                'IFNULL(SUM(count), 0) AS count',
                'IFNULL(SUM(realCount), 0) AS realCount',
                'IFNULL(SUM(active), 0) AS active',
                'IFNULL(SUM(realActive), 0) AS realActive'
            ])
            ->where(['date' => $date])
            ->get();
    }*/

    /**
     * 获取范围内统计
     * @param $keyword
     */
    public function getSum($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);

        return $this
            ->field([
                'IFNULL(SUM(downClick), 0) AS downClick',
            ])
            ->where($where)
            ->get();
    }
}