<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Navigation\AdTypeModel;
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
 * Class AdType
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-广告分类 Admin/Navigation/AdType")
 * @ApiGroupDescription("后台广告分类相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class AdType extends AdminBase
{
    /**
     * 广告分类列表
     * @Api(name="广告分类列表",path="/Api/Admin/Navigation/AdType/typeList")
     * @ApiDescription("广告分类列表")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess({"code":200,"result":[{"adTypeId":1,"adTypeName":"默认","conversionRate":100,"status":1},{"adTypeId":2,"adTypeName":"播放器","conversionRate":20,"status":1},{"adTypeId":3,"adTypeName":"直播","conversionRate":10,"status":1},{"adTypeId":4,"adTypeName":"炮台","conversionRate":2,"status":1},{"adTypeId":5,"adTypeName":"博彩","conversionRate":2,"status":1}],"systemTimestamp":1693988501,"systemDateTime":"2023-09-06 16:21:41","msg":"OK"})
     */
    public function typeList()
    {
        try {
            $data = AdTypeModel::create()
                ->field(['adTypeId', 'adTypeName', 'conversionRate', 'status'])
                ->where(['status' => [AdTypeModel::STATE_DELETED, '>']])
                ->all();

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告分类添加
     * @Api(name="广告分类添加",path="/Api/Admin/Navigation/AdType/add")
     * @ApiDescription("广告分类添加")
     * @Method(allow=["POST"])
     * @Param(name="adTypeName", alias="广告分类名字", type="string", required="", mbLengthMin="1", description="广告分类名字")
     * @Param(name="conversionRate", alias="转化率", type="float", required="", min="0", max="100", description="转化率百分比0-100")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":6,"systemTimestamp":1693990585,"systemDateTime":"2023-09-06 16:56:25","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'adTypeName' => $param['adTypeName'],
                'conversionRate' => floatval($param['conversionRate']),
                'status' => intval($param['status']),
            ];

            $result = AdTypeModel::create($data)->save();

            if (!$result) {
                throw new Exception('广告分类添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }
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
     * 广告分类修改
     * @Api(name="广告分类修改",path="/Api/Admin/Navigation/AdType/edit")
     * @ApiDescription("广告分类修改")
     * @Method(allow=["POST"])
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @Param(name="adTypeName", alias="广告分类名字", type="string", required="", mbLengthMin="1", description="广告分类名字")
     * @Param(name="conversionRate", alias="转化率", type="float", required="", min="0", max="100", description="转化率百分比0-100")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1693990585,"systemDateTime":"2023-09-06 16:56:25","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'adTypeId' => $param['adTypeId'],
                'adTypeName' => $param['adTypeName'],
                'conversionRate' => floatval($param['conversionRate']),
                'status' => intval($param['status']),
            ];


            // 这里获取的是当前数据，用作对比判断。
            $adType = AdTypeModel::create()->where(['status' => [AdTypeModel::STATE_DELETED, '>']])->get($data['adTypeId']);
            if (!$adType) {
                throw new Exception('无效的广告分类id', Status::CODE_BAD_REQUEST);
            }

            $result = AdTypeModel::create()->where(['adTypeId' => $adType->adTypeId])->update($data);

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
     * 广告分类修改状态
     * @Api(name="广告分类修改状态",path="/Api/Admin/Navigation/AdType/setStatus")
     * @ApiDescription("广告分类修改状态")
     * @Method(allow=["POST"])
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $adType = AdTypeModel::create()->where(['status' => [AdTypeModel::STATE_DELETED, '>']])->get($param['adTypeId']);

            if (!$adType) {
                throw new Exception('无效的广告分类id', Status::CODE_BAD_REQUEST);
            }

            $result = $adType->update(['status' => intval($param['status'])]);

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
     * 广告分类删除
     * @Api(name="广告分类删除",path="/Api/Admin/Navigation/AdType/delete")
     * @ApiDescription("广告分类删除")
     * @Method(allow=["POST"])
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $adType = AdTypeModel::create()->where(['status' => [AdTypeModel::STATE_DELETED, '>']])->get($param['adTypeId']);

            if (!$adType) {
                throw new Exception('无效的广告分类id', Status::CODE_BAD_REQUEST);
            }

            // 目前是仅修改状态，然后删除上传的文件。以保留点击统计表中的关联数据。
            $result = $adType->update(['status' => AdTypeModel::STATE_DELETED]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_DELETE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }
}