<?php

namespace App\Service\Live;

use App\Model\Live\LiveModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class LiveService
{
    use Singleton;

    public function addLive($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $liveId = LiveModel::create($data)->save();

            if ($liveId === false) {
                throw new Exception('直播添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $liveId;
    }

    public function editLive($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $live = LiveModel::create()->get([
                'liveId' => $data['liveId'],
                'status' => [LiveModel::STATE_DELETED, '>'],
            ]);

            if (!$live) {
                throw new Exception('无效的直播id', Status::CODE_BAD_REQUEST);
            }

            $result = $live->update($data);

            if ($result === false) {
                throw new Exception('直播修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}