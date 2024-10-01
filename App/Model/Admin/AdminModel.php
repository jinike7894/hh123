<?php

namespace App\Model\Admin;

use App\Component\CommonStatusInterface;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\Hash;
use PHPGangsta_GoogleAuthenticator;

/**
 * Class AdminModel
 * Create With Automatic Generator
 * @property $adminId int | id
 * @property $parentAdminId int | 父id
 * @property $roleId string | 角色id 多个用逗号分割
 * @property $merchantId int | 商户id
 * @property $adminNickname string | 用户名
 * @property $adminAccount string | 用户登录名
 * @property $adminPassword string | 用户密码
 * @property $adminType enum | 后台管理员还是商户
 * @property $avatar string | 用户头像（同样是商户头像）
 * @property $adminEmail string | 邮箱
 * @property $adminMobile string | 手机
 * @property $lastLoginIpLong int | 最后登录IP
 * @property $lastLoginTime datetime | 最后登录时间
 * @property $status int | 用户状态 -1删除 0禁用 1正常
 * @property $googleAuthenticatorSecret string | google令牌
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class AdminModel extends AbstractModel implements CommonStatusInterface
{
    protected $tableName = 'admin';
    protected $primaryKey = 'adminId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const DEFAULT_PASSWORD = 'qwe123';

    /**
     * @getAll
     * @keyword adminName
     * @param int  page  1
     * @param string  keyword
     * @param int  pageSize  10
     * @param string $field *
     * @return array[total,list]
     */
    public function getAll(int $page = 1,  $keyword = [], int $pageSize = 10, $field = '*'): array
    {
        $where = [];
        if (!empty($keyword)) {
            isset($keyword['parentAdminId']) && $where['parentAdminId'] = $keyword['parentAdminId'];
            isset($keyword['merchantId']) && $where['merchantId'] = $keyword['merchantId'];
            !empty($keyword['adminType']) && $where['adminType'] = $keyword['adminType'];
            !empty($keyword['adminId']) && $where['adminId'] = $keyword['adminId'];
            !empty($keyword['adminAccount']) && $where['adminAccount'] = $keyword['adminAccount'];
            isset($keyword['status']) && ($keyword['status'] != 2) && $where['status'] = $keyword['status'];
        }

        $field = 'adminId,adminAccount,adminNickname as nickname,roleId,adminType,lastLoginIpLong,lastLoginTime,status,googleAuthenticatorSecret';
        $list = $this->limit($pageSize * ($page - 1), $pageSize)
            ->order($this->primaryKey, 'DESC')
            ->withTotalCount()
            ->field($field)
            ->all($where);
        foreach ($list as $v) {
            $v->lastLoginIp = long2ip($v->lastLoginIpLong);
            $roleId = explode(",", $v->roleId);
            $roleIds = RoleModel::create()->where('roleId', $roleId, 'IN')->field('group_concat(roleName) as roles')->get();
            $v->roles = $roleIds->roles;
            switch ($v->status) {
                case self::STATE_DELETED:
                    $v->status_name = '已删除';
                    break;
                case self::STATE_FORBIDDEN:
                    $v->status_name = '禁用';
                    break;
                case self::STATE_NORMAL:
                    $v->status_name = '正常';
                    break;
            }
            $v->isGoogleAuthenticator = !empty($v->googleAuthenticatorSecret) ? 1 : 0;
            $v->hidden('googleAuthenticatorSecret');
        }
        $total = $this->lastQueryResult()->getTotalCount();

        return ['total' => $total, 'list' => $list];
    }

    /**
     * @return AuthModel|array|bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getAuth()
    {
        // 角色权限
        $roleIds = explode(',', $this->roleId);

        // 管理员 全部权限
        if (in_array(1, $roleIds)) {
            $auths = AuthModel::create()
                ->field('authId,parentAuthId,authName,authRule,authIcon,authType')
                ->all();
            return $auths;
        }
        $authIds = RolesAuthsRelationModel::create()
            ->where('roleId', $roleIds, 'IN')
            ->column('authId');

        $authIds = array_unique(array_filter($authIds));

        if (!empty($authIds)) {
            $list = AuthModel::create()
                ->field('authId,parentAuthId,authName,authRule,authIcon,authType')
                ->where('authId', $authIds, 'IN')
                ->all();
            return $list;
        }
        return [];
    }

    public function getAuthMenu()
    {
        // 角色权限
        $roleIds = explode(',', $this->roleId);

        // 管理员 全部权限
        if (in_array(1, $roleIds)) {
            $auths = AuthModel::create()
                ->field('authId,parentAuthId,authName,authRule,authIcon,authType')
                ->where('authType', 0)
                ->all();
            return $auths;
        }
        $authIds = RolesAuthsRelationModel::create()
            ->where('roleId', $roleIds, 'IN')
            ->column('authId');

        $authIds = array_unique(array_filter($authIds));

        if (!empty($authIds)) {
            $list = AuthModel::create()
                ->field('authId,parentAuthId,authName,authRule,authIcon,authType')
                ->where('authId', $authIds, 'IN')
                ->where('authType', 0)
                ->all();
            return $list;
        }
        return [];
    }

    public function getOne($adminId, $field = '*')
    {
        $admin = $this->alias('a')->field($field)->join('merchant m', 'a.merchantId = m.merchantId', 'LEFT')->get($adminId);
        $roleIds = explode(',', $admin->roleId);

        if (!$roleIds) {
            throw new \Exception('该账号没有绑定角色', Status::CODE_BAD_REQUEST);
        }

        $roleList = RoleModel::create()
            ->where('roleId', $roleIds, 'IN')
            ->all();

        $admin->hidden([
            'roleId',
            'adminPassword',
            'lastLoginIpLong',
            'securePassword'
        ]);
        return ['admin' => $admin, 'role' => $roleList];
    }
}
