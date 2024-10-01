<?php

namespace App\Service\Market;

use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use Exception;
use Throwable;

class CommonOrderService
{
    use Singleton;

    public function handleOrderCallback($orderNo)
    {
        switch (true) {
            case strncmp($orderNo, 'VIP', 3) === 0:
                $result = VipOrderService::getInstance()->handleOrderCallback($orderNo);
                break;
            default:
                throw new Exception('未知的订单号类型', Status::CODE_BAD_REQUEST);
        }

        return $result;
    }
}