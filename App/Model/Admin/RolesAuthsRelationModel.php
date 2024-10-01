<?php

namespace App\Model\Admin;

use EasySwoole\ORM\AbstractModel;

/**
 * Class RoleModel
 * Create With Automatic Generator
 * @property $roleId int | 用户id
 * @property $role_name string | 角色名
 * @property $role_status int | 角色状态 0正常1禁用
 * @property $level int | 角色级别 越小权限越高
 * @property $createTime int | 创建时间
 * @property $updateTime int | 更新时间
 */
class RolesAuthsRelationModel extends AbstractModel
{
    protected $tableName = 'roles_auths_relation';

    /**
     * @getAll
     * @param int $page 1
     * @param int $pageSize 10
     * @param string $field *
     * @return array[total,list]
     */
    public function getAll(int $page = 1, int $pageSize = 10, string $field = '*'): array
    {
        $list = $this
            ->withTotalCount()
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->limit($pageSize * ($page - 1), $pageSize)
            ->all();
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }

}

