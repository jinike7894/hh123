<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserInviteModel
 * @package App\Model\User
 * @property $recordId int | id
 * @property $userId int | 用户id
 * @property $type int | 1图片2视频3脱衣
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserVideoRecordModel extends BaseModel
{
    protected $tableName = 'user_video_record';

    protected $primaryKey = 'recordId';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';


}