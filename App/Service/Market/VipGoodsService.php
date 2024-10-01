<?php

namespace App\Service\Market;

use App\Model\User\UserVipGoodsModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class VipGoodsService
{
    use Singleton;

    public function editVipGoods($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $vipGoods = UserVipGoodsModel::create()->get([
                'goodsId' => $data['goodsId'],
                'status' => [UserVipGoodsModel::STATE_DELETED, '>'],
            ]);

            if (!$vipGoods) {
                throw new Exception('无效的VIP商品id', Status::CODE_BAD_REQUEST);
            }

            $result = $vipGoods->update($data);

            if ($result === false) {
                throw new Exception('VIP商品修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}