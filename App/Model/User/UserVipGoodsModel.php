<?php

namespace App\Model\User;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class UserVipGoodsModel
 * @package App\Model\User
 * @property $goodsId int | 商品id
 * @property $goodsKey string | 商品key
 * @property $goodsType string | 商品类型
 * @property $goodsName string | 商品名
 * @property $goodsIntroduction string | 商品介绍
 * @property $goodsOriginalPrice decimal | 商品原价
 * @property $goodsPresentPrice decimal | 商品现价
 * @property $days int | 生效天数
 * @property $sort int | 排序（正序）
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class UserVipGoodsModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'user_vip_goods';

    protected $primaryKey = 'goodsId';
    protected $createTime = 'createTime';
    protected $autoTimeStamp = 'datetime';
    protected $updateTime = 'updateTime';

    const TYPE_COMMON = 'Common';
    const TYPE_NEW_USER = 'NewUser';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['goodsId']) && $keyword['goodsId']) {
            if (is_array($keyword['goodsId'])) {
                $where['goodsId'] = [$keyword['goodsId'], 'IN'];
            } else {
                $where['goodsId'] = $keyword['goodsId'];
            }
        }

        if (isset($keyword['goodsType']) && $keyword['goodsType']) {
            if (is_array($keyword['goodsType'])) {
                $where['goodsType'] = [$keyword['goodsType'], 'IN'];
            } else {
                $where['goodsType'] = $keyword['goodsType'];
            }
        }

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }
}