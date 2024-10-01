<?php

namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class TypeModel extends BaseModel implements CommonStatusInterface
{
    const MID_VIDEO = 1;
    const MID_ADULT_VIDEO = 16;

    const MODULE_ADULT = 'Adult';
    const MODULE_HOME = 'Home';

    const MODULE_TYPE_EN = [
        self::MODULE_ADULT => 'fuli',
        self::MODULE_HOME => 'shouyefuli',
    ];

    protected $tableName = 'mac_type';

    protected $primaryKey = 'type_id';

}