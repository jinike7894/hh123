<?php

namespace App\HttpController\Api\Market;

use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\User\UserVipGoodsModel;
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
 * @package App\HttpController\Api\Market
 * @ApiGroup(groupName="市场-VipGoods Market/VipGoods")
 * @ApiGroupDescription("市场-VipGoods相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class VipGoods extends UserBase
{
    /**
     * VIP商品列表
     * @Api(name="VIP商品列表",path="/Api/Market/VipGoods/vipGoodsList")
     * @ApiDescription("VIP商品列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"goodsId":5,"goodsKey":"Forever","goodsType":"NewUser","goodsName":"新人限时永久卡","goodsIntroduction":"新人限时永久卡","goodsOriginalPrice":"300.00","goodsPresentPrice":"100.00","days":36500,"sort":0,"status":1,"remainTime":85971},{"goodsId":1,"goodsKey":"Month","goodsType":"Common","goodsName":"月卡","goodsIntroduction":"30天看VIP视频","goodsOriginalPrice":"499.00","goodsPresentPrice":"50.00","days":30,"sort":10,"status":1,"remainTime":0},{"goodsId":2,"goodsKey":"Quarter","goodsType":"Common","goodsName":"季卡","goodsIntroduction":"90天看VIP视频","goodsOriginalPrice":"999.00","goodsPresentPrice":"100.00","days":90,"sort":20,"status":1,"remainTime":0},{"goodsId":3,"goodsKey":"Year","goodsType":"Common","goodsName":"年卡","goodsIntroduction":"365天看VIP视频","goodsOriginalPrice":"1999.00","goodsPresentPrice":"200.00","days":365,"sort":30,"status":1,"remainTime":0},{"goodsId":4,"goodsKey":"Forever","goodsType":"Common","goodsName":"永久卡","goodsIntroduction":"永久看VIP视频","goodsOriginalPrice":"2999.00","goodsPresentPrice":"300.00","days":36500,"sort":40,"status":1,"remainTime":0}],"systemTimestamp":1702051047,"systemDateTime":"2023-12-08 23:57:27","msg":"OK"})
     */
    public function vipGoodsList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [
                'status' => UserVipGoodsModel::STATE_NORMAL,
            ];

            $user = UserModel::create()->get($this->who['userId']);
            $remainTime = 86400 - (time() - strtotime($user->createTime));

            if ($remainTime > 0) {
                $keyword['goodsType'] = [UserVipGoodsModel::TYPE_COMMON, UserVipGoodsModel::TYPE_NEW_USER];
            } else {
                $keyword['goodsType'] = [UserVipGoodsModel::TYPE_COMMON];
            }

            $vipGoods = UserVipGoodsModel::create();
            $where = $vipGoods->parseKeywordToWhere($keyword);

            $data = $vipGoods
                ->field([
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
                ])
                ->where($where)
                ->order('sort', 'ASC')
                ->all();

            if ($remainTime > 0) {
                foreach ($data as $datum) {
                    if ($datum['goodsType'] == UserVipGoodsModel::TYPE_NEW_USER) {
                        $datum['remainTime'] = $remainTime;
                    } else {
                        $datum['remainTime'] = 0;
                    }
                }
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * ai商品列表
     * @Api(name="ai商品列表",path="/Api/Market/VipGoods/aiGoodsList")
     * @ApiDescription("ai商品列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"goodsId":6,"goodsKey":"AiFaceImg","goodsType":"AiFaceImg","goodsName":"Ai图片换脸","goodsIntroduction":"Ai图片换脸3次","goodsOriginalPrice":"30.00","goodsPresentPrice":"30.00","sort":0,"status":1,"times":3},{"goodsId":7,"goodsKey":"AiFaceVideo","goodsType":"AiFaceVideo","goodsName":"Ai视频换脸","goodsIntroduction":"Ai视频换脸6次","goodsOriginalPrice":"30.00","goodsPresentPrice":"30.00","sort":0,"status":1,"times":3},{"goodsId":8,"goodsKey":"AiPicture","goodsType":"AiPicture","goodsName":"Ai脱衣","goodsIntroduction":"Ai脱衣3次","goodsOriginalPrice":"30.00","goodsPresentPrice":"30.00","sort":0,"status":1,"times":3}],"systemTimestamp":1707899272,"systemDateTime":"2024-02-14 16:27:52","msg":"OK"})
     */
    public function aiGoodsList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [
                'status' => UserVipGoodsModel::STATE_NORMAL,
            ];

            $aiType = ['AiFaceImg','AiFaceVideo','AiPicture'];
            $keyword['goodsType'] = 'AiFaceImg';
            $vipGoods = UserVipGoodsModel::create();
            $where = $vipGoods->parseKeywordToWhere($keyword);

            $data1 = $vipGoods
                ->field([
                    'goodsId',
                    'goodsKey',
                    'goodsType',
                    'goodsName',
                    'goodsIntroduction',
                    'goodsOriginalPrice',
                    'goodsPresentPrice',
                    'days as times',
                    'sort',
                    'status',
                ])
                ->where($where)
                ->order('sort', 'ASC')
                ->all();

            $keyword['goodsType'] = 'AiFaceVideo';
            $vipGoods = UserVipGoodsModel::create();
            $where = $vipGoods->parseKeywordToWhere($keyword);

            $data2 = $vipGoods
                ->field([
                    'goodsId',
                    'goodsKey',
                    'goodsType',
                    'goodsName',
                    'goodsIntroduction',
                    'goodsOriginalPrice',
                    'goodsPresentPrice',
                    'days as times',
                    'sort',
                    'status',
                ])
                ->where($where)
                ->order('sort', 'ASC')
                ->all();

            $keyword['goodsType'] = 'AiPicture';
            $vipGoods = UserVipGoodsModel::create();
            $where = $vipGoods->parseKeywordToWhere($keyword);

            $data3 = $vipGoods
                ->field([
                    'goodsId',
                    'goodsKey',
                    'goodsType',
                    'goodsName',
                    'goodsIntroduction',
                    'goodsOriginalPrice',
                    'goodsPresentPrice',
                    'days as times',
                    'sort',
                    'status',
                ])
                ->where($where)
                ->order('sort', 'ASC')
                ->all();

            $new = [
                [
                    'type' => 1,
                    'goodsList' => $data1,
                ],
                [
                    'type' => 2,
                    'goodsList' => $data2,
                ],
                [
                    'type' => 3,
                    'goodsList' => $data3,
                ],
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $new, Status::getReasonPhrase(Status::CODE_OK));
    }
}