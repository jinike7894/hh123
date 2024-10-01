<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserAiFaceRecordModel
 * @package App\Model\User
 * @property $facerecordId int | id
 * @property $userId int | 用户id
 * @property $recordCode string | 系统唯一code
 * @property $type int | 1图片脱衣 2视频脱衣
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserAiFaceRecordModel extends BaseModel
{
    protected $tableName = 'user_ai_face_record';

    protected $primaryKey = 'facerecordId';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';


}