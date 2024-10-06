<?php

namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class ShortVideoDyUserModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_short_vod_dy_user';
    protected $primaryKey = 'id';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';
    const NODELETE = 0;
    const DELETED = 1;
   

}