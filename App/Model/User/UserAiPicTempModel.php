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
class UserAiPicTempModel extends BaseModel
{
    protected $tableName = 'user_ai_pic_temp';
    protected $primaryKey = 'pictempId';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';


}