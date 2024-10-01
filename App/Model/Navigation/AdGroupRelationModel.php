<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;

/**
 * Class AdGroupRelationModel
 * @package App\Model\Navigation
 * @property $adGroupId int | 广告组id
 * @property $adId int | 广告id
 * @property $sort int | 这个广告在这个组的排序0-255，正序排列
 */
class AdGroupRelationModel extends BaseModel
{
    protected $tableName = 'nav_ad_group_relation';

    protected $primaryKey = ['adGroupId', 'adId'];

}