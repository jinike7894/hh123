<?php

namespace App\Model\Admin;

use EasySwoole\ORM\AbstractModel;

/**
 * Class AuthModel
 * @package App\Model\Admin
 * @property $authId int | id
 * @property $parentAuthId int | id
 * @property $authName string | 权限名
 * @property $authRule string | 路由地址
 * @property $authController string | 路由控制器
 * @property $authAction string | 路由方法
 * @property $authType int | 权限类型 0菜单1按钮
 * @property $isLog int | 是否记录日志(0 不记录 1记录)
 * @property $authIcon string | 路由图标
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class AuthModel extends AbstractModel
{
    protected $tableName = 'auths';

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
        $total = $this->lastQueryResult()->getTotalCount();;
        return ['total' => $total, 'list' => $list];
    }
}

