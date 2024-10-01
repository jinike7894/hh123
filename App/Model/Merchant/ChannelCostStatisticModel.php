<?php

namespace App\Model\Merchant;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class ChannelModel
 * @package App\Model\Merchant
 * @property $chanelCostId int | 渠道成本id
 * @property $channelId int | 渠道id
 * @property $channelKey string | 渠道key
 * @property $cost float | 保底单价
 * @property $apiUrl string | 渠道域名
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ChannelCostStatisticModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'ch_channel_cost_statistics';

    protected $primaryKey = 'channelCostId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';
}