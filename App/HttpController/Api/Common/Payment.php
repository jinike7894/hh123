<?php

namespace App\HttpController\Api\Common;

use App\HttpController\Api\ApiBase;
use App\HttpController\Router;
use App\Service\Payment\PaymentService;
use App\Utility\LogHandler;
use EasySwoole\Component\Context\ContextManager;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;

/**
 * Class Payment
 * @package App\HttpController\Api\Common
 * @ApiGroup(groupName="公共 支付 Common/Payment")
 * @ApiGroupDescription("支付相关的操作")
 */
class Payment extends ApiBase
{

    /**
     * 支付回调
     * @Api(name="支付回调",path="/Api/Common/Payment/callback")
     * @ApiDescription("支付回调")
     * @Param(name="objName", alias="对象名", type="string", optional="", mbLengthMin="1", description="对象名")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess()
     */
    public function callback()
    {
        try {
            $param = $this->request()->getRequestParam();
            $content = $this->request()->getBody()->__toString();
            // $contentArr = json_decode($content, true);
            // var_dump(ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY));

            $ip = $this->clientRealIP();

            // 这个是测试用的
            if (Core::getInstance()->runMode() == 'dev' && isset($param['ip'])) {
                $ip = $param['ip'];
            }

            $objName = $param['objName'] ?? '';
            // 这里判断如果对象名参数不是参数中带的，则使用路由匹配参数。
            if (!$objName) {
                $parseParams = ContextManager::getInstance()->get(Router::PARSE_PARAMS_CONTEXT_KEY);
                $objName = $parseParams['objName'] ?? '';
            }

            LogHandler::getInstance()->logCustomFile('收到支付回调ip:' . $ip . ' param:' . json_encode($param, 256) . ' body:' . $content, 'Payment/' . $objName . '/callback', LogHandler::LOG_LEVEL_INFO, 'info');

            $paymentObj = PaymentService::getInstance()->load($objName);

            unset($param['objName']);
            unset($param['ip']);
            $response = $paymentObj->handleCallback($param, $content, $ip);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), '', $e->getMessage());
        }

        return $this->response()->write($response);
    }
}