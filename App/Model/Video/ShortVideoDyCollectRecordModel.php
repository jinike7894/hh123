<?php
namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class ShortVideoDyCollectRecordModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_short_vod_collect_record';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

   

}