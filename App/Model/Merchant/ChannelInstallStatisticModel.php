<?php

namespace App\Model\Merchant;

use App\Model\BaseModel;

/**
 * Class ChannelInstallStatisticModel
 * @package App\Model\Merchant
 * @property $date date | 日期
 * @property $channelId int | 渠道id
 * @property $installAndroid int | 安卓虚假安装计数
 * @property $realInstallAndroid int | 安卓真实安装计数
 * @property $activeAndroid int | 安卓虚假活跃计数
 * @property $realActiveAndroid int | 安卓真实活跃计数
 * @property $installIOS int | IOS虚假安装计数
 * @property $realInstallIOS int | IOS真实安装计数
 * @property $activeIOS int | IOS虚假活跃计数
 * @property $realActiveIOS int | IOS真实活跃计数
 * @property $installIOSBookmark int | IOS书签虚假安装计数
 * @property $realInstallIOSBookmark int | IOS书签真实安装计数
 * @property $activeIOSBookmark int | IOS书签虚假活跃计数
 * @property $realActiveIOSBookmark int | IOS书签真实活跃计数
 * @property $installTotal int | 总计虚假安装计数
 * @property $realInstallTotal int | 总计真实安装计数
 * @property $activeTotal int | 总计虚假活跃计数
 * @property $realActiveTotal int | 总计真实活跃计数
 */
class ChannelInstallStatisticModel extends BaseModel
{
    protected $tableName = 'ch_channel_install_statistic';

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
                'IFNULL(SUM(installAndroid), 0) AS installAndroid',
                'IFNULL(SUM(realInstallAndroid), 0) AS realInstallAndroid',
                'IFNULL(SUM(activeAndroid), 0) AS activeAndroid',
                'IFNULL(SUM(realActiveAndroid), 0) AS realActiveAndroid',

                'IFNULL(SUM(installIOS), 0) AS installIOS',
                'IFNULL(SUM(realInstallIOS), 0) AS realInstallIOS',
                'IFNULL(SUM(activeIOS), 0) AS activeIOS',
                'IFNULL(SUM(realActiveIOS), 0) AS realActiveIOS',

                'IFNULL(SUM(installIOSBookmark), 0) AS installIOSBookmark',
                'IFNULL(SUM(realInstallIOSBookmark), 0) AS realInstallIOSBookmark',
                'IFNULL(SUM(activeIOSBookmark), 0) AS activeIOSBookmark',
                'IFNULL(SUM(realActiveIOSBookmark), 0) AS realActiveIOSBookmark',

                'IFNULL(SUM(installTotal), 0) AS installTotal',
                'IFNULL(SUM(realInstallTotal), 0) AS realInstallTotal',
                'IFNULL(SUM(activeTotal), 0) AS activeTotal',
                'IFNULL(SUM(realActiveTotal), 0) AS realActiveTotal',
            ])
            ->where($where)
            ->get();
    }
}