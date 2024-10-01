<?php

namespace App\HttpController\Api\Admin\Common;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Common\SendCodeModel;
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
use Throwable;

/**
 * Class SendCode
 * @package App\HttpController\Api\Admin\Common
 * @ApiGroup(groupName="后台-公共-验证码 Admin/Common/SendCode")
 * @ApiGroupDescription("验证码相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class SendCode extends AdminBase
{
    /**
     * 验证码列表
     * @Api(name="验证码列表",path="/Api/Admin/Common/SendCode/sendCodeList")
     * @ApiDescription("验证码列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="ip", alias="ip", type="string", optional="", mbLengthMin="1", description="ip")
     * @Param(name="target", alias="发送目标", type="string", optional="", mbLengthMin="1", description="发送目标")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"id":1,"type":"MS","channel":"JSMS","requestIp":"172.18.0.1","target":"13408489122","content":"{'code':6694}","response":"","status":1,"createTime":"2023-11-27 17:22:45"}],"options":{"status":1}},"systemTimestamp":1701083662,"systemDateTime":"2023-11-27 19:14:22","msg":"OK"})
     */
    public function sendCodeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['ip']) && $keyword['requestIpLong'] = ip2long(trim($param['ip']));
            isset($param['target']) && $keyword['target'] = trim($param['target']);
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'id',
                'type',
                'channel',
                'requestIp',
                'target',
                'content',
                'response',
                'status',
                'createTime',
            ];

            $data = SendCodeModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}