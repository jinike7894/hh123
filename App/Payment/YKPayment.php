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

class YKPayment extends PaymentBase
{
    public $payName = 'YKPayment';

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

        if (!isset($params['tradeType'])) {
            throw new Exception('未正确配置渠道编号', Status::CODE_BAD_REQUEST);
        }

        $postData = [
            'merchantId' => $this->mchId,
            'merchantPayNo' => $data['orderNo'],
            'tradeType' => $params['tradeType'],
            'amt' => $data['amount'],
            'notifyUrl' => $this->getCallbackUrl($this->payName),
            'goodsName' => $data['goodsName'],
            'payIp' => $data['ip'],
        ];

        $sign = $this->getSign($postData, $this->secretKey);
        $postData['sign'] = $sign;

        LogHandler::getInstance()->logCustomFile(json_encode($postData, JSON_UNESCAPED_UNICODE), 'Payment/' . $this->payName . '/createOrderRequest');
        file_put_contents('paylog.log', json_encode($postData)."\r\n",FILE_APPEND);
        $response = Func::httpsRequest($this->createOrderUrl, $postData, 'post');
        LogHandler::getInstance()->logCustomFile($response, 'Payment/' . $this->payName . '/createOrderResponse');
        file_put_contents('paylog.log', $response."\r\n",FILE_APPEND);
        $responseArr = json_decode($response, true);

        if ($responseArr['code'] != '0000') {
            throw new Exception($responseArr['message'], Status::CODE_BAD_REQUEST);
        }

        /*
          {
            "code": "0000",
            "message": "成功",
            "data": {
                "tradeAmt": "10.00",
                "payNo": "1556455888970337551",
                "merchantPayNo": "1556455889894",
                "createTime": "2019-04-28 20:51:28",
                "fee": "0.44",
                "sign": "5B937C6B8D4526463C4A5F373A0CD204",
                "tradeType": "ZFBNATIVE",
                "url": "http://119.3.107.99:8989/channel/payGateway?payNo=1556455888970337551",
                "actualAmt": "9.56"
            }
        }
         */
        // 这里一定要注意返回的字段要满足要求
        return [
            'tradeNo' => $responseArr['data']['payNo'],
            'payUrl' => $responseArr['data']['url'],
            'expiredTime' => time() + 300,
        ];
    }

    public function handleCallback($param, $body, $ip)
    {
        try {
            /*
             {
                "payNo": 1556455888970337551,
                "merchantPayNo": "PAY15564558898944556415",
                "tradeType": "ZFBWAP",
                "tradeAmt": "10.00",
                "fee": "0.44",
                "actualAmt": "9.56",
                "tradeStatus": 1,
                "sign": "33B37BC886011F2284E9812DDFAF3ED8",
            }
             */
            file_put_contents('paylog.log', json_encode($param)."\r\n",FILE_APPEND);
            $sign = $this->getSign($param, $this->secretKey);
            if ($param['sign'] != $sign) {
                throw new Exception('回调签名验证失败', Status::CODE_BAD_REQUEST);
            }

            $orderNo = $param['merchantPayNo'] ?? '';
            if (!$orderNo) {
                throw new Exception('未获取到正确的订单号', Status::CODE_BAD_REQUEST);
            }

            // 这里有个支付状态可以先验证
            if ($param['tradeStatus'] != 1) {
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
            if ($k != 'sign' && !empty($v) && $k != 'attach') {
                $str .= $k . '=' . $v . '&';
            }
        }

        $sign = strtoupper(md5($str . 'key=' . $secretKey));
        return $sign;
    }
}