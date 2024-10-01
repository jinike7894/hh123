<?php

namespace App\Service\Payment;

use App\Payment\PaymentBase;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use Exception;
use Throwable;

class PaymentService
{
    use Singleton;

    /**
     * @param string $paymentClass
     * @return PaymentBase
     * @throws \Exception
     */
    public function load($paymentClass)
    {
        $paymentClass = trim($paymentClass);

        if (class_exists("App\Payment\\{$paymentClass}")) {
            // var_dump("App\Payment\\{$paymentClass}");
            // 这不能用单例，会G，应该用对象池。所以获取方法不应该是getInstance
            //$instance = call_user_func_array(["App\Payment\\{$paymentClass}", 'getInstance'], []);
            $class = "App\Payment\\{$paymentClass}";
            $instance = new $class();
        } else {
            throw new Exception('不存在的通道', Status::CODE_BAD_REQUEST);
        }
        return $instance;
    }
}