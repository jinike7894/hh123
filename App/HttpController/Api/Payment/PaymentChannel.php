<?php

namespace App\HttpController\Api\Payment;

use App\HttpController\Api\User\UserBase;
use App\Model\Payment\PaymentChannelModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use Exception;
use Throwable;

/**
 * Class PaymentChannel
 * @package App\HttpController\Api\Payment
 * @ApiGroup(groupName="支付渠道 Payment/PaymentChannel")
 * @ApiGroupDescription("支付渠道相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class PaymentChannel extends UserBase
{

    /**
     * 支付渠道列表
     * @Api(name="支付渠道列表",path="/Api/Payment/PaymentChannel/paymentChannelList")
     * @ApiDescription("支付渠道列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="source", alias="来源", type="string", optional="", description="来源")
     * @ApiSuccess({"code":200,"result":[{"paymentTypeId":1,"typeName":"支付宝","typeKey":"Alipay","availableChannelsRelation":[{"paymentChannelId":1,"paymentTypeId":1,"channelName":"支付宝综合原生-H5","channelAlias":"","min":10,"max":1000},{"paymentChannelId":2,"paymentTypeId":1,"channelName":"支付宝超级原生","channelAlias":"","min":10,"max":2000},{"paymentChannelId":3,"paymentTypeId":1,"channelName":"支付宝快手","channelAlias":"","min":10,"max":2000},{"paymentChannelId":4,"paymentTypeId":1,"channelName":"支付宝YY","channelAlias":"","min":10,"max":500},{"paymentChannelId":5,"paymentTypeId":1,"channelName":"支付宝扫码转账","channelAlias":"","min":50,"max":10000}]},{"paymentTypeId":2,"typeName":"微信","typeKey":"Wechat","availableChannelsRelation":[{"paymentChannelId":6,"paymentTypeId":2,"channelName":"微信超级原生","channelAlias":"","min":10,"max":500}]}],"systemTimestamp":1701766810,"systemDateTime":"2023-12-05 17:00:10","msg":"OK"})
     */
    public function paymentChannelList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = PaymentChannelModel::create()->getAvailableList();
            if(!isset($param['source'])){
                foreach ($data as $key => $item) {
                    // 遍历 availableChannelsRelation 数组
                    foreach ($item['availableChannelsRelation'] as $channel_key => $channel) {
                        // 如果 channelName 是 "微信原生"，则删除该元素
                        if ($channel['channelName'] === '微信原生') {
                            unset($data[$key]['availableChannelsRelation'][$channel_key]);
                        }
                    }
                    // 重新设置 availableChannelsRelation 数组的键
                    $data[$key]['availableChannelsRelation'] = array_values($data[$key]['availableChannelsRelation']);
                }
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}