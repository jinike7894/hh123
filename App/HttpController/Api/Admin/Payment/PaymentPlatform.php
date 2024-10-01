<?php

namespace App\HttpController\Api\Admin\Payment;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Payment\PaymentPlatformModel;
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
 * Class PaymentPlatform
 * @package App\HttpController\Api\Admin\Payment
 * @ApiGroup(groupName="后台-支付-支付平台 Admin/Payment/PaymentPlatform")
 * @ApiGroupDescription("后台支付平台相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class PaymentPlatform extends AdminBase
{
    /**
     * 支付平台列表
     * @Api(name="支付平台列表",path="/Api/Admin/Payment/PaymentPlatform/paymentPlatformList")
     * @ApiDescription("支付平台列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"paymentPlatformId":1,"platformName":"天汇","platformObj":"TianHui","status":1}],"options":[]},"systemTimestamp":1702043615,"systemDateTime":"2023-12-08 21:53:35","msg":"OK"})
     */
    public function paymentPlatformList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'paymentPlatformId',
                'platformName',
                'platformObj',
                'status',
            ];

            $data = PaymentPlatformModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 支付平台全关联列表
     * @Api(name="支付平台全关联列表",path="/Api/Admin/Payment/PaymentPlatform/paymentPlatformAllList")
     * @ApiDescription("支付平台全关联列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"paymentPlatformId":1,"platformName":"天汇","platformObj":"TianHui"}],"systemTimestamp":1702043876,"systemDateTime":"2023-12-08 21:57:56","msg":"OK"})
     */
    public function paymentPlatformAllList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            $field = [
                'paymentPlatformId',
                'platformName',
                'platformObj',
            ];

            $data = PaymentPlatformModel::create()
                ->field($field)
                ->where([
                    'status' => [PaymentPlatformModel::STATE_DELETED, '>']
                ])
                ->setDefaultOrder()
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}