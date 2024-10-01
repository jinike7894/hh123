<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class ZoneModel
 * @package App\Model\Navigation
 * @property $zoneId int | id
 * @property $zoneName string | 广告位名
 * @property $zoneKey string | 广告位键
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ZoneModel extends BaseModel
{
    protected $tableName = 'nav_zone';

    protected $primaryKey = 'zoneId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';
}