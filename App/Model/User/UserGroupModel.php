<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserGroupModel
 * @package App\Model\User
 * @property $userGroupId int | 用户组id
 * @property $userGroupName string | 用户组名称
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserGroupModel extends BaseModel
{
    protected $tableName = 'userGroup';

    protected $primaryKey = 'userGroupId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    // 系统预定义的组id
    const GROUP_TOURIST_ID = 1;
    const GROUP_ORDINARY_ID = 2;
    const GROUP_VIP_ID = 3;
}