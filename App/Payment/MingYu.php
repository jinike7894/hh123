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

class MingYu extends PaymentBase
{
    public $payName = 'MingYu';

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

        if (!isset($params['productId'])) {
            throw new Exception('未正确配置渠道编号', Status::CODE_BAD_REQUEST);
        }

        $postData = [
            'mchId' => $this->mchId,
            'productId' => $params['productId'],
            'subject' => $data['goodsName'],
            'body' => $data['goodsName'],
            'mchOrderNo' => $data['orderNo'],
            'amount' => $data['amount'] * 100, // 这个支付的单位是分
            'clientIp' => $data['ip'],
            'extra' => 'abcd',
            'notifyUrl' => $this->getCallbackUrl($this->payName),
        ];

        $sign = $this->getSign($postData, $this->secretKey);
        $postData['sign'] = $sign;

        LogHandler::getInstance()->logCustomFile(json_encode($postData, JSON_UNESCAPED_UNICODE), 'Payment/' . $this->payName . '/createOrderRequest');
        $response = Func::httpsRequest($this->createOrderUrl, $postData, 'post');
        LogHandler::getInstance()->logCustomFile($response, 'Payment/' . $this->payName . '/createOrderResponse');
        $responseArr = json_decode($response, true);

        if ($responseArr['retCode'] != 'SUCCESS') {
            throw new Exception($responseArr['retMsg'], Status::CODE_BAD_REQUEST);
        }

        /*
          {
            "retCode": SUCCESS,
            "payOrderId",
            "payParams" : {payMethod: "formJump", payUrl: "支付Url" },
            "sign"
        }
         */
        // 这里一定要注意返回的字段要满足要求
        return [
            'tradeNo' => $responseArr['payOrderId'],
            'payUrl' => $responseArr['payParams']['payUrl'],
            'expiredTime' => 0,
        ];
    }

    public function handleCallback($param, $body, $ip)
    {
        try {
            /**
             * 字段名    变量名    必填    签名    类型    示例值    描述
             * 支付订单号    payOrderId    是    是    String(30)    P20160427210604000490    支付中心生成的订单号
             * 商户ID    mchId    是    是    String(30)    20001222    支付中心分配的商户号
             * 支付产品ID    productId    是    是    int    8001    支付产品ID
             * 商户订单号    mchOrderNo    是    是    String(30)    20160427210604000490    商户生成的订单号
             * 支付金额    amount    是    是    int    100    支付金额,单位分
             * 实际支付金额    income    是    是    int    100    实际支付金额,单位分
             * 状态    status    是    是    int    1    支付状态,0-订单生成,1-支付中,2-支付成功,3-业务处理完成
             * 渠道订单号    channelOrderNo    否    是    String(64)    wx2016081611532915ae15beab0167893571    三方支付渠道订单号
             * 渠道数据包    channelAttach    否    是    String    {"bank_type":"CMB_DEBIT","trade_type":"pay.weixin.micropay"}    支付渠道数据包
             * 扩展参数1    param1    否    是    String(64)        支付中心回调时会原样返回
             * 扩展参数2    param2    否    是    String(64)        支付中心回调时会原样返回
             * 支付成功时间    paySuccTime    是    是    long        精确到毫秒
             * 通知类型    backType    是    是    int    1    通知类型，1-前台通知，2-后台通知
             * 签名    sign    是    否    String(32)    C380BEC2BFD727A4B6845133519F3AD6    签名值，详见签名算法
             */

            $sign = $this->getSign($param, $this->secretKey);
            if ($param['sign'] != $sign) {
                throw new Exception('回调签名验证失败', Status::CODE_BAD_REQUEST);
            }

            $orderNo = $param['mchOrderNo'] ?? '';
            if (!$orderNo) {
                throw new Exception('未获取到正确的订单号', Status::CODE_BAD_REQUEST);
            }

            // 这里有个支付状态可以先验证
            if ($param['status'] != 2) {
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