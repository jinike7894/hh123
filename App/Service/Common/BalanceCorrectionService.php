<?php

namespace App\Service\Common;

use App\Enum\BalanceChangeType;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class BalanceCorrectionService
{
    use Singleton;

    /**
     * @param $param
     * @return bool
     * @throws Throwable
     */
    public function correct($param)
    {
        try {
            if ($param['amount'] == 0) {
                throw new Exception('要调整的金额不能为0', Status::CODE_BAD_REQUEST);
            }

            DbManager::getInstance()->startTransactionWithCount();

            // 里面有判断用户是否有效
            $who = Func::getWho($param['whoId'], $param['whoType']);

            $balanceChange = null;

            // 这样写是为了后面增加类型的扩展，不要觉得奇怪为啥不写else
            if ($param['amount'] > 0) {
                $balanceChange = $who->updateBalance($param['amount'], BalanceChangeType::TYPE_MANUAL_ADD, $param['remark']);
            }

            if ($param['amount'] < 0) {
                $balanceChange = $who->updateBalance($param['amount'], BalanceChangeType::TYPE_MANUAL_REDUCE, $param['remark']);
            }

            if (!$balanceChange) {
                throw new Exception('操作失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}