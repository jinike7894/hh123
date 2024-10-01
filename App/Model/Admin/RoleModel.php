<?php

namespace App\Model\Admin;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;

/**
 * Class RoleModel
 * Create With Automatic Generator
 * @property $roleId int | id
 * @property $roleName string | 角色名
 * @property $remark string | 权限描述
 * @property $isGoogleAuth int | google验证
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class RoleModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'roles';
    protected $primaryKey = 'roleId';

    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const ID_SUPER = 1;
    const ID_ADMIN = 2;
    const ID_MERCHANT = 3;

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['roleId']) && $keyword['roleId']) {
            if (is_array($keyword['roleId'])) {
                $where['roleId'] = [$keyword['roleId'], 'IN'];
            } else {
                $where['roleId'] = $keyword['roleId'];
            }
        }

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    public function insertAll($data = [])
    {
        RolesAuthsRelationModel::create()->func(function (QueryBuilder $builder, $data) {
            $builder->insertAll($this->tableName, $data);
        });
    }
}
