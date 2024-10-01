<?php

namespace App\Service\Payment;

use App\Model\Payment\PaymentPlatformModel;
use App\RedisKey\Payment\PaymentKey;
use EasySwoole\Component\Singleton;
use EasySwoole\RedisPool\RedisPool;

class PaymentPlatformService
{
    use Singleton;

    public function getPlatformByObj($platformObj)
    {
        $redis = RedisPool::defer();
        $key = PaymentKey::platform($platformObj);
        $data = $redis->get($key);

        if (!$data) {
            $platform = PaymentPlatformModel::create()->get(['platformObj' => $platformObj]);
            $data = $platform->toRawArray();
            $data['platformData'] = json_decode($data['platformData'], true);

            $redis->setEx($key, 600, $data);
        }

        return $data;
    }
}