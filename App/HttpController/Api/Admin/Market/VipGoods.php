<?php

namespace App\HttpController\Api\Admin\Market;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\User\UserVipGoodsModel;
use App\Service\Market\VipGoodsService;
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
 * Class VipGoods
 * @package App\HttpController\Api\Admin\Market
 * @ApiGroup(groupName="后台-用户-VIP商品 Admin/Market/VipGoods")
 * @ApiGroupDescription("后台VIP商品相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class VipGoods extends AdminBase
{
    /**
     * vip商品列表
     * @Api(name="vip商品列表",path="/Api/Admin/Market/VipGoods/vipGoodsList")
     * @ApiDescription("vip商品列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="goodsId", alias="vip商品id", type="int", optional="", min="1", description="vip商品id")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["goodsId_DESC", "goodsId_ASC", "sort_DESC", "sort_ASC"], description="1.商品id（goodsId）2.排序（sort）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":5,"list":[{"goodsId":5,"goodsKey":"Forever","goodsType":"NewUser","goodsName":"新人限时永久卡","goodsIntroduction":"新人限时永久卡","goodsOriginalPrice":"300.00","goodsPresentPrice":"100.00","days":36500,"sort":0,"status":1},{"goodsId":4,"goodsKey":"Forever","goodsType":"Common","goodsName":"永久卡","goodsIntroduction":"永久看VIP视频","goodsOriginalPrice":"2999.00","goodsPresentPrice":"300.00","days":36500,"sort":40,"status":1},{"goodsId":3,"goodsKey":"Year","goodsType":"Common","goodsName":"年卡","goodsIntroduction":"365天看VIP视频","goodsOriginalPrice":"1999.00","goodsPresentPrice":"200.00","days":365,"sort":30,"status":1},{"goodsId":2,"goodsKey":"Quarter","goodsType":"Common","goodsName":"季卡","goodsIntroduction":"90天看VIP视频","goodsOriginalPrice":"999.00","goodsPresentPrice":"100.00","days":90,"sort":20,"status":1},{"goodsId":1,"goodsKey":"Month","goodsType":"Common","goodsName":"月卡","goodsIntroduction":"30天看VIP视频","goodsOriginalPrice":"499.00","goodsPresentPrice":"50.00","days":30,"sort":10,"status":1}],"options":[]},"systemTimestamp":1702051168,"systemDateTime":"2023-12-08 23:59:28","msg":"OK"})
     */
    public function vipGoodsList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['goodsId']) && $keyword['goodsId'] = intval($param['goodsId']);
            isset($param['status']) && $keyword['status'] = trim($param['status']);

            $field = [
                'goodsId',
                'goodsKey',
                'goodsType',
                'goodsName',
                'goodsIntroduction',
                'goodsOriginalPrice',
                'goodsPresentPrice',
                'days',
                'sort',
                'status',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = UserVipGoodsModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * vip商品详情
     * @Api(name="vip商品详情",path="/Api/Admin/Market/VipGoods/vipGoodsDetail")
     * @ApiDescription("vip商品详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="goodsId", alias="vip商品id", type="int", required="", min="1", description="vip商品id")
     * @ApiSuccess({"code":200,"result":{"goodsId":1,"goodsKey":"Month","goodsName":"悦享月卡","goodsIntroduction":"31天看VIP视频<br />抢会员专属福利","goodsOriginalPrice":"999.00","goodsPresentPrice":"50.00","days":30,"sort":10,"status":1,"createTime":"2023-12-04 22:06:12","updateTime":"2023-12-06 14:21:31"},"systemTimestamp":1701868443,"systemDateTime":"2023-12-06 21:14:03","msg":"OK"})
     */
    public function vipGoodsDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $vipGoods = UserVipGoodsModel::create()
                ->get([
                    'goodsId' => $param['goodsId'],
                    'status' => [UserVipGoodsModel::STATE_DELETED, '>'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $vipGoods, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * vip商品编辑
     * @Api(name="vip商品编辑",path="/Api/Admin/Market/VipGoods/edit")
     * @ApiDescription("vip商品编辑")
     * @Method(allow=["POST"])
     * @Param(name="goodsId", alias="vip商品id", type="int", required="", min="1", description="vip商品id")
     * @Param(name="goodsKey", alias="vip商品key", type="int", required="", mbLengthMin="1", description="vip商品key")
     * @Param(name="goodsName", alias="商品名", type="string", required="", mbLengthMin="1", mbLengthMax="20", description="商品名")
     * @Param(name="goodsIntroduction", alias="商品介绍", type="string", required="", mbLengthMin="0", mbLengthMax="100", description="商品介绍")
     * @Param(name="goodsOriginalPrice", alias="商品原价", type="float", required="", min="0", description="商品原价")
     * @Param(name="goodsPresentPrice", alias="商品现价", type="float", required="", min="0", description="商品现价")
     * @Param(name="days", alias="生效天数", type="int", required="", min="0", description="生效天数")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'goodsId' => intval($param['goodsId']),
                'goodsKey' => trim($param['goodsKey']),
                'goodsName' => trim($param['goodsName']),
                'goodsIntroduction' => trim($param['goodsIntroduction']),
                'goodsOriginalPrice' => floatval($param['goodsOriginalPrice']),
                'goodsPresentPrice' => floatval($param['goodsPresentPrice']),
                'days' => intval($param['days']),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
            ];

            $result = VipGoodsService::getInstance()->editVipGoods($data);

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
     * vip商品修改状态
     * @Api(name="vip商品修改状态",path="/Api/Admin/Market/VipGoods/setStatus")
     * @ApiDescription("vip商品修改状态")
     * @Method(allow=["POST"])
     * @Param(name="goodsId", alias="vip商品id", type="int", required="", min="1", description="vip商品id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'goodsId' => $param['goodsId'],
                'status' => intval($param['status']),
            ];

            $result = VipGoodsService::getInstance()->editVipGoods($data);

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