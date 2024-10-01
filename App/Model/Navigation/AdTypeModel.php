<?php

namespace App\Model\Navigation;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class AdTypeModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'nav_ad_type';

    protected $primaryKey = 'adTypeId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';
}