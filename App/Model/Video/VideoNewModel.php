<?php
namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class VideoNewModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_vod';
    protected $primaryKey = 'vod_id';

}