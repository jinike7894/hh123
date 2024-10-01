<?php

namespace App\Model\User;

use App\Model\BaseModel;

/**
 * Class UserInviteModel
 * @package App\Model\User
 * @property $userInviteId int | 邀请id
 * @property $inviterId int | 邀请人用户id
 * @property $inviteeId int | 被邀请人用户id
 * @property $createDate date | 创建日期
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserInviteModel extends BaseModel
{
    protected $tableName = 'user_invite';

    protected $primaryKey = 'userInviteId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['ui.inviterId']) && $keyword['ui.inviterId'] > 0 && $where['ui.inviterId'] = $keyword['ui.inviterId'];
        isset($keyword['inviterId']) && $keyword['inviterId'] > 0 && $where['inviterId'] = $keyword['inviterId'];

        return $where;
    }
}