<?php

namespace App\Payment;

use App\Model\Payment\PaymentWhiteListModel;
use App\RedisKey\Payment\PaymentKey;
use App\Service\Market\CommonOrderService;
use App\Service\Payment\PaymentPlatformService;
use App\Utility\Func;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class LongSheng extends PaymentBase
{
    public $payName = 'LongSheng';

    private $mchId;
    private $createOrderUrl;
    private $secretKey;

    public function __construct()
    {
        // 配置都在数据库中
        $platform = PaymentPlatformService::getInstance()->getPlatformByObj($this->payName);
        $this->mchId = $platform['platformData']['mchId'];
        $this->createOrderUrl = $platform['platformData']['createOrderUrl'];
        $this->secretKey = $platform['platformData']['secretKey'];
    }

    public function createOrder($data)
    {
        // 先拿渠道号
        $paymentChannel = &$data['paymentChannel'];
        $params = json_decode($paymentChannel['params'], true);

        if (!isset($params['payType'])) {
            throw new Exception('未正确配置渠道编号', Status::CODE_BAD_REQUEST);
        }

        $postData = [
            'merchantNo' => $this->mchId,
            'payType' => $params['payType'],
            'attach' => $data['goodsName'],
            'body' => $data['goodsName'],
            'outTradeNo' => $data['orderNo'],
            'payMoney' => $data['amount'] * 100, // 这个支付的单位是分
            'spbillIp' => $data['ip'],
            'notifyUrl' => $this->getCallbackUrl($this->payName),
        ];

        $sign = $this->getSign($postData, $this->secretKey);
        $postData['sign'] = $sign;

        LogHandler::getInstance()->logCustomFile(json_encode($postData, JSON_UNESCAPED_UNICODE), 'Payment/' . $this->payName . '/createOrderRequest');
        $response = Func::httpsRequest($this->createOrderUrl, $postData, 'post');
        LogHandler::getInstance()->logCustomFile($response, 'Payment/' . $this->payName . '/createOrderResponse');
        $responseArr = json_decode($response, true);

        if ($responseArr['code'] != 200) {
            throw new Exception($responseArr['message'], Status::CODE_BAD_REQUEST);
        }

        /*
          {
            "code": 200,
            "message": "成功",
            "data": {
                "payUrl": "https://xxx.com/pay/gopay?OrderNO=xxx"
            }
        }
         */
        // 这里一定要注意返回的字段要满足要求
        return [
            'tradeNo' => "",
            'payUrl' => $responseArr['data']['payUrl'],
            'expiredTime' => 0,
        ];
    }

    public function handleCallback($param, $body, $ip)
    {
        try {
            /*
              {
                "code": 200,
                "message": "成功",
                "data": {
                  "resultCode": "SUCCESS", //订单支付结果
                  "msg": "支付成功", //订单支付结果描述
                  "merchantNo": "000000", //龍晟支付分配的商户号
                  "body": "body" ,
                  "attach": "attach",
                  "payMoney": "100", //订单金额 以分为单位
                  "actualMoney": "99.99", //订单实际支付金额  以分为单位
                  "orderNo": "xxxxxxxxx",  //龍晟支付平台的唯一支付订单号
                  "outTradeNo": "xxxxxxxxx",  //商户唯一订单号
                  "payTime": "xxxxxxxxx",  //支付成功时间
                  "sign": "xxxxxxxxxxxxxxxxxxxx", //签名串
                }
              }
              ps:只有data内层数据参与签名
             */

            $bodyParam = json_decode($body, true);
            $bodyParam = $bodyParam['data'];

            $sign = $this->getSign($bodyParam, $this->secretKey);
            if ($bodyParam['sign'] != $sign) {
                throw new Exception('回调签名验证失败', Status::CODE_BAD_REQUEST);
            }

            $orderNo = $bodyParam['outTradeNo'] ?? '';
            if (!$orderNo) {
                throw new Exception('未获取到正确的订单号', Status::CODE_BAD_REQUEST);
            }

            // 这里有个支付状态可以先验证
            if ($bodyParam['resultCode'] != 'SUCCESS') {
                throw new Exception('订单未支付成功', Status::CODE_BAD_REQUEST);
            }

            $lockKey = PaymentKey::lock($orderNo);
            $lockValue = RedisLock::lock($lockKey);

            DbManager::getInstance()->startTransactionWithCount();

            // 验证白名单
            $whiteIp = PaymentWhiteListModel::create()->isExists($this->payName, ip2long($ip));
            if (!$whiteIp) {
                throw new Exception('当前ip无权限', Status::CODE_BAD_REQUEST);
            }

            CommonOrderService::getInstance()->handleOrderCallback($orderNo);

            DbManager::getInstance()->commitWithCount();
            RedisLock::releaseLock($lockKey, $lockValue);
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            if (isset($lockKey) && isset($lockValue)) {
                RedisLock::releaseLock($lockKey, $lockValue);
            }
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return 'SUCCESS';
    }

    public function getSign($data, $secretKey)
    {
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            if ($k != 'sign' && !empty($v)) {
                $str .= $k . '=' . $v . '&';
            }
        }
        $sign = strtoupper(md5($str . 'key=' . $secretKey));
        return $sign;
    }
}