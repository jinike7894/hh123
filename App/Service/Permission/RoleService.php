<?php

namespace App\Service\Permission;

use App\Model\Admin\RoleModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class RoleService
{
    use Singleton;

    public function addRole($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $id = RoleModel::create($data)->save();

            if ($id === false) {
                throw new Exception('角色添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $id;
    }

    public function editRole($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $model = RoleModel::create()->get([
                'roleId' => $data['roleId'],
                'status' => [RoleModel::STATE_DELETED, '>'],
            ]);

            if (!$model) {
                throw new Exception('无效的角色id', Status::CODE_BAD_REQUEST);
            }

            $result = $model->update($data);

            if ($result === false) {
                throw new Exception('角色修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}