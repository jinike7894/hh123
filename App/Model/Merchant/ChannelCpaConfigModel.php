<?php

namespace App\Model\Merchant;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class ChannelModel
 * @package App\Model\Merchant
 * @property $cpaConfigId int | cpa配置记录Id
 * @property $date string | 日期
 * @property $channelId int | 渠道id
 * @property $cpaCost float | cpa单价
 * @property $coefficient float | 点击系数
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ChannelCpaConfigModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'ch_channel_cpa_config';

    protected $primaryKey = 'cpaConfigId';
}