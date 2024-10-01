<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserInviteModel
 * @package App\Model\User
 * @property $id int | id
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserAiVideoTempModel extends BaseModel
{
    protected $tableName = 'user_ai_video_temp';
    protected $primaryKey = 'videotempId';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';


}