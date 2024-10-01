<?php

namespace App\Service\Prostitute;

use App\Model\Prostitute\ProstituteTypeModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class ProstituteTypeService
{
    use Singleton;

    public function editProstituteType($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $prostituteType = ProstituteTypeModel::create()->get([
                'prostituteTypeId' => $data['prostituteTypeId'],
                'status' => [ProstituteTypeModel::STATE_DELETED, '>'],
            ]);

            if (!$prostituteType) {
                throw new Exception('无效的楼凤类型id', Status::CODE_BAD_REQUEST);
            }

            $result = $prostituteType->update($data);

            if ($result === false) {
                throw new Exception('楼凤类型修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}