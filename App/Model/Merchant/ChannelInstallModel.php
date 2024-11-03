<?php

namespace App\Model\Merchant;

use App\Model\BaseModel;

/**
 * Class ChannelInstallModel
 * @package App\Model\Merchant
 * @property $channelInstallId int | 安装id
 * @property $channelId int | 渠道id
 * @property $source enum | 来源
 * @property $ipLong int | ipLong
 * @property $ip string | ipv4
 * @property $deviceId string | 设备id
 * @property $operatingSystem string | 操作系统
 * @property $operatingSystemVersion string | 系统版本
 * @property $isCounted int | 1.计数 0.未计数，因为有扣量所以详情中要标记。
 * @property $latestActiveDate date | 最近活跃日期
 * @property $createDate date | 创建日期
 * @property $createTimeBucketHour int | 时间区间（小时）
 * @property $createTimeBucketHalfHour int | 时间区间（半小时）
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ChannelInstallModel extends BaseModel
{
    protected $tableName = 'ch_channel_install';

    protected $primaryKey = 'channelInstallId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const SOURCE_ANDROID = 'Android';
    const SOURCE_IOS_BOOKMARK = 'IOSBookmark';
    const SOURCE_IOS = 'IOS';

    /**
     * 算作是app的类型
     */
    const SOURCE_APP_LIST = [
        self::SOURCE_ANDROID,
        self::SOURCE_IOS,
        self::SOURCE_IOS_BOOKMARK,
    ];

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
        isset($keyword['source']) && $keyword['source'] && $where['source'] = $keyword['source'];
        isset($keyword['createDate']) && $keyword['createDate'] && $where['createDate'] = $keyword['createDate'];

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['createDateStart'])) {
            $this->where('createDate', $keyword['createDateStart'], '>=');
        }
        if (isset($keyword['createDateEnd'])) {
            $this->where('createDate', $keyword['createDateEnd'], '<=');
        }

        return $where;
    }
}