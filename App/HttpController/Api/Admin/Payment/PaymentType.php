<?php

namespace App\HttpController\Api\Admin\Payment;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Payment\PaymentPlatformModel;
use App\Model\Payment\PaymentTypeModel;
use App\Service\Payment\PaymentService;
use App\Service\Payment\PaymentTypeService;
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
 * Class PaymentType
 * @package App\HttpController\Api\Admin\Payment
 * @ApiGroup(groupName="后台-支付-支付类型 Admin/Payment/PaymentType")
 * @ApiGroupDescription("后台支付类型相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class PaymentType extends AdminBase
{
    /**
     * 支付类型列表
     * @Api(name="支付类型列表",path="/Api/Admin/Payment/PaymentType/paymentTypeList")
     * @ApiDescription("支付类型列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"paymentTypeId":2,"typeName":"微信","typeKey":"Wechat","sort":20,"status":1},{"paymentTypeId":1,"typeName":"支付宝","typeKey":"Alipay","sort":10,"status":1}],"options":[]},"systemTimestamp":1702122675,"systemDateTime":"2023-12-09 19:51:15","msg":"OK"})
     */
    public function paymentTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'paymentTypeId',
                'typeName',
                'typeKey',
                'sort',
                'status',
            ];

            $data = PaymentTypeModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 支付类型全关联列表
     * @Api(name="支付类型全关联列表",path="/Api/Admin/Payment/PaymentType/paymentTypeAllList")
     * @ApiDescription("支付类型全关联列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"paymentTypeId":1,"typeName":"支付宝","typeKey":"Alipay"},{"paymentTypeId":2,"typeName":"微信","typeKey":"Wechat"}],"systemTimestamp":1702044978,"systemDateTime":"2023-12-08 22:16:18","msg":"OK"})
     */
    public function paymentTypeAllList()
    {
        $param = $this->request()->getRequestParam();

        try {

            $field = [
                'paymentTypeId',
                'typeName',
                'typeKey',
            ];

            $data = PaymentTypeModel::create()
                ->field($field)
                ->where([
                    'status' => [PaymentTypeModel::STATE_DELETED, '>']
                ])
                ->order('sort', 'ASC')
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 支付类型编辑
     * @Api(name="支付类型编辑",path="/Api/Admin/Payment/PaymentType/edit")
     * @ApiDescription("支付类型编辑")
     * @Method(allow=["POST"])
     * @Param(name="paymentTypeId", alias="支付类型id", type="int", required="", min="1", description="支付类型id")
     * @Param(name="typeName", alias="类型名", type="string", required="", mbLengthMin="1", mbLengthMax="16", description="类型名")
     * @Param(name="typeKey", alias="类型Key", type="string", required="", mbLengthMin="1", mbLengthMax="16", description="类型Key")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'paymentTypeId' => intval($param['paymentTypeId']),
                'typeName' => trim($param['typeName']),
                'typeKey' => trim($param['typeKey']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = PaymentTypeService::getInstance()->editPaymentType($data);

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
     * 支付类型修改状态
     * @Api(name="支付类型修改状态",path="/Api/Admin/Payment/PaymentType/setStatus")
     * @ApiDescription("支付类型修改状态")
     * @Method(allow=["POST"])
     * @Param(name="paymentTypeId", alias="支付类型id", type="int", required="", min="1", description="支付类型id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'paymentTypeId' => $param['paymentTypeId'],
                'status' => intval($param['status']),
            ];

            $result = PaymentTypeService::getInstance()->editPaymentType($data);

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