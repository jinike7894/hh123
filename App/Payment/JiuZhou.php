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

class JiuZhou extends PaymentBase
{
    public $payName = 'JiuZhou';

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

        if (!isset($params['bankCode'])) {
            throw new Exception('未正确配置渠道编号', Status::CODE_BAD_REQUEST);
        }

        $postData = [
            'pay_memberid' => $this->mchId,
            'pay_orderid' => $data['orderNo'],
            'pay_applydate' => date('Y-m-d H:i:s'),
            'pay_bankcode' => $params['bankCode'],
            'pay_notifyurl' => $this->getCallbackUrl($this->payName),
            'pay_callbackurl' => 'https://baidu.com',
            'pay_amount' => $data['amount'],
        ];

        $sign = $this->getSign($postData, $this->secretKey);
        $postData['pay_md5sign'] = $sign;
        $postData['format'] = 'json';

        LogHandler::getInstance()->logCustomFile(json_encode($postData, JSON_UNESCAPED_UNICODE), 'Payment/' . $this->payName . '/createOrderRequest');
        $response = Func::httpsRequest($this->createOrderUrl, $postData, 'post');
        LogHandler::getInstance()->logCustomFile($response, 'Payment/' . $this->payName . '/createOrderResponse');
        $responseArr = json_decode($response, true);

        if ($responseArr['status'] != 'success') {
            throw new Exception($responseArr['msg'], Status::CODE_BAD_REQUEST);
        }

        /*
array(5) {
  ["status"]=>
  string(7) "success"
  ["out_trade_no"]=>
  string(28) "R202312211611366583f338b57fc"
  ["total_fee"]=>
  string(5) "50.00"
  ["real_fee"]=>
  string(5) "50.00"
  ["qrcode_url"]=>
  string(84) "http://dying2.jingxin900.com/index.php/index/index/order?id=WDIwMjMxMjIxMTYxMTM2T3pV"
}
         */
        // 这里一定要注意返回的字段要满足要求
        return [
            'tradeNo' => $responseArr['out_trade_no'],
            'payUrl' => $responseArr['qrcode_url'],
            'expiredTime' => 0,
        ];
    }

    public function handleCallback($param, $body, $ip)
    {
        try {
            /**
             * 参数名称    参数含义    参与签名    参数说明
             * memberid    商户编号    是
             * orderid    订单号    是
             * amount    订单金额    是
             * transaction_id    交易流水号    是
             * datetime    交易时间    是
             * returncode    交易状态    是    “00” 为成功
             * attach    扩展返回    否    商户附加数据返回
             * sign    签名    否    请看验证签名字段格式
             */

            $sign = $this->getSign($param, $this->secretKey);
            if ($param['sign'] != $sign) {
                throw new Exception('回调签名验证失败', Status::CODE_BAD_REQUEST);
            }

            $orderNo = $param['orderid'] ?? '';
            if (!$orderNo) {
                throw new Exception('未获取到正确的订单号', Status::CODE_BAD_REQUEST);
            }

            // 这里有个支付状态可以先验证
            if ($param['returncode'] != '00') {
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

        return 'OK';
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