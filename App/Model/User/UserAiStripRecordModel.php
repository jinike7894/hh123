<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserAiFaceRecordModel
 * @package App\Model\User
 * @property $striprecordId int | id
 * @property $userId int | 用户id
 * @property $recordCode string | 系统唯一code
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserAiStripRecordModel extends BaseModel
{
    protected $tableName = 'user_ai_strip_record';

    protected $primaryKey = 'striprecordId';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';


}