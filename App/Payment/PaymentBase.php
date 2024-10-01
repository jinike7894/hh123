<?php

namespace App\Payment;

use App\Enum\ConfigKey\AppConfigKey;
use App\Model\Common\ConfigModel;
use EasySwoole\Http\Message\Status;
use Exception;

abstract class PaymentBase
{
    public $payName;

    abstract public function createOrder($data);

    abstract public function handleCallback($param, $body, $ip);

    /**
     * 获取发送给平台的回调域名
     * @param string $objName 支付对象名
     * @return string
     */
    protected function getCallbackUrl($objName)
    {
        $domain = $_SERVER['HTTP_HOST'] ?? '';
        if ($domain) {
            $prefix = $this->getHttpOrHttps() . $domain;
        } else {
            $apiDomain = ConfigModel::create()->getConfigValue(AppConfigKey::API_DOMAIN);
            $apiDomain = explode(';', $apiDomain);
            $prefix = isset($apiDomain[0]) ? trim($apiDomain[0]) : '';
        }

        if (!$prefix) {
            throw new Exception('未获取到Api地址', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        // 这个在 Router.php 文件中定义了路由，注意不能单独使用。
        return $prefix . '/Api/Common/Payment/callback/' . $objName;
    }

    protected function getHttpOrHttps()
    {
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            return 'https://';
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return 'https://';
        }

        return 'http://';
    }

}