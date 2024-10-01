<?php

namespace App\Model\User;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class UserModel
 * @package App\Model\User
 * @property $userId int | 用户id
 * @property $userGroupId int | 用户组id
 * @property $userGroupExpiryDate string | 用户组有效日期
 * @property $userName string | 用户名（账号）
 * @property $password string | 密码
 * @property $pageId int | 注册来源页面id
 * @property $channelId int | 注册来源渠道id
 * @property $phoneCountryCode string | 手机号国家编码
 * @property $phoneNumber string | 手机号
 * @property $deviceId string | 设备id
 * @property $nickname string | 昵称
 * @property $balance decimal | 余额
 * @property $avatar string | 头像
 * @property $regIpLong int | 注册ipLong
 * @property $regDate string | 注册日期
 * @property $lastLoginIpLong int | 最后登录IPLong
 * @property $lastLoginTime string | 最后登录时间
 * @property $remark string | 备注
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'user';

    protected $primaryKey = 'userId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function updateLastLoginInfo($ipLong, $time)
    {
        return $this->update(['lastLoginTime' => $time, 'lastLoginIpLong' => $ipLong]);
    }
}