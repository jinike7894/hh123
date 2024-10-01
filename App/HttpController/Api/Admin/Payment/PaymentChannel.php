<?php

namespace App\HttpController\Api\Admin\Payment;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Payment\PaymentChannelModel;
use App\Model\Payment\PaymentPlatformModel;
use App\Model\Payment\PaymentTypeModel;
use App\Service\Payment\PaymentChannelService;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use Exception;
use Throwable;

/**
 * Class PaymentChannel
 * @package App\HttpController\Api\Admin\Payment
 * @ApiGroup(groupName="后台-支付-支付渠道 Admin/Payment/PaymentChannel")
 * @ApiGroupDescription("后台支付渠道相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class PaymentChannel extends AdminBase
{
    /**
     * 支付渠道列表
     * @Api(name="支付渠道列表",path="/Api/Admin/Payment/PaymentChannel/paymentChannelList")
     * @ApiDescription("支付渠道列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="paymentChannelId", alias="支付渠道id", type="int", optional="", min="1", description="支付渠道id")
     * @Param(name="paymentPlatformId", alias="支付平台id", type="int", optional="", min="1", description="支付平台id")
     * @Param(name="paymentTypeId", alias="支付分类id", type="int", optional="", min="1", description="支付分类id")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["paymentChannelId_DESC", "paymentChannelId_ASC", "sort_DESC", "sort_ASC"], description="1.id倒叙（paymentChannelId_DESC）2.id正叙（paymentChannelId_ASC）3.sort倒叙（sort_DESC）4.sort正叙（sort_ASC）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":6,"list":[{"paymentChannelId":6,"paymentPlatformId":1,"paymentTypeId":2,"channelName":"微信超级原生","channelAlias":"","min":10,"max":500,"params":"","sort":60,"status":1,"platformName":"天汇","typeName":"微信"}],"options":[]},"systemTimestamp":1701851779,"systemDateTime":"2023-12-06 16:36:19","msg":"OK"})
     */
    public function paymentChannelList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['paymentChannelId']) && $keyword['paymentChannelId'] = $param['paymentChannelId'];
            isset($param['paymentPlatformId']) && $keyword['paymentPlatformId'] = $param['paymentPlatformId'];
            isset($param['paymentTypeId']) && $keyword['paymentTypeId'] = $param['paymentTypeId'];
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'paymentChannelId',
                'paymentPlatformId',
                'paymentTypeId',
                'channelName',
                'channelAlias',
                'min',
                'max',
                'params',
                'sort',
                'status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = PaymentChannelModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = PaymentPlatformModel::create()->appendInfo($data['list'], ['platformName'], 'paymentPlatformId', 'paymentPlatformId');
            $data['list'] = PaymentTypeModel::create()->appendInfo($data['list'], ['typeName'], 'paymentTypeId', 'paymentTypeId');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 支付渠道详情
     * @Api(name="支付渠道详情",path="/Api/Admin/Payment/PaymentChannel/paymentChannelDetail")
     * @ApiDescription("支付渠道详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="paymentChannelId", alias="支付渠道id", type="int", required="", min="1", description="支付渠道id")
     * @ApiSuccess({"code":200,"result":{"paymentChannelId":1,"paymentPlatformId":1,"paymentTypeId":1,"channelName":"支付宝综合原生-H5","channelAlias":"","min":10,"max":1000,"params":"","sort":10,"status":0,"createTime":"1000-01-01 00:00:00","updateTime":"2023-12-08 18:53:05"},"systemTimestamp":1702034048,"systemDateTime":"2023-12-08 19:14:08","msg":"OK"})
     */
    public function paymentChannelDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = PaymentChannelModel::create()->get($param['paymentChannelId']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 支付渠道添加
     * @Api(name="支付渠道添加",path="/Api/Admin/Payment/PaymentChannel/add")
     * @ApiDescription("支付渠道添加")
     * @Method(allow=["POST"])
     * @Param(name="paymentPlatformId", alias="支付平台id", type="int", required="", min="1", description="支付平台id")
     * @Param(name="paymentTypeId", alias="支付分类id", type="int", required="", min="1", description="支付分类id")
     * @Param(name="channelName", alias="渠道名", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="渠道名")
     * @Param(name="channelAlias", alias="渠道别名", type="string", optional="", mbLengthMin="0", mbLengthMax="32", description="渠道别名")
     * @Param(name="min", alias="最小限额", type="int", required="", min="0", description="最小限额")
     * @Param(name="max", alias="最大限额", type="int", required="", min="0", description="最大限额")
     * @Param(name="params", alias="渠道参数", type="string", required="", mbLengthMin="0", description="渠道参数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'paymentPlatformId' => intval($param['paymentPlatformId']),
                'paymentTypeId' => intval($param['paymentTypeId']),
                'channelName' => trim($param['channelName']),
                'channelAlias' => trim($param['channelAlias']),
                'min' => intval($param['min']),
                'max' => intval($param['max']),
                'params' => trim($param['params']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = PaymentChannelService::getInstance()->addPaymentChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_ADD,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 支付渠道编辑
     * @Api(name="支付渠道编辑",path="/Api/Admin/Payment/PaymentChannel/edit")
     * @ApiDescription("支付渠道编辑")
     * @Method(allow=["POST"])
     * @Param(name="paymentChannelId", alias="支付渠道id", type="int", required="", min="1", description="支付渠道id")
     * @Param(name="paymentPlatformId", alias="支付平台id", type="int", required="", min="1", description="支付平台id")
     * @Param(name="paymentTypeId", alias="支付分类id", type="int", required="", min="1", description="支付分类id")
     * @Param(name="channelName", alias="渠道名", type="string", required="", mbLengthMin="1", mbLengthMax="32", description="渠道名")
     * @Param(name="channelAlias", alias="渠道别名", type="string", optional="", mbLengthMin="0", mbLengthMax="32", description="渠道别名")
     * @Param(name="min", alias="最小限额", type="int", required="", min="0", description="最小限额")
     * @Param(name="max", alias="最大限额", type="int", required="", min="0", description="最大限额")
     * @Param(name="params", alias="渠道参数", type="string", required="", mbLengthMin="0", description="渠道参数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'paymentChannelId' => intval($param['paymentChannelId']),
                'paymentPlatformId' => intval($param['paymentPlatformId']),
                'paymentTypeId' => intval($param['paymentTypeId']),
                'channelName' => trim($param['channelName']),
                'channelAlias' => trim($param['channelAlias']),
                'min' => intval($param['min']),
                'max' => intval($param['max']),
                'params' => trim($param['params']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = PaymentChannelService::getInstance()->editPaymentChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 支付渠道修改状态
     * @Api(name="支付渠道修改状态",path="/Api/Admin/Payment/PaymentChannel/setStatus")
     * @ApiDescription("支付渠道修改状态")
     * @Method(allow=["POST"])
     * @Param(name="paymentChannelId", alias="支付渠道id", type="int", required="", min="1", description="支付渠道id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'paymentChannelId' => $param['paymentChannelId'],
                'status' => intval($param['status']),
            ];

            $result = PaymentChannelService::getInstance()->editPaymentChannel($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
}