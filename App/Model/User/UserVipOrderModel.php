<?php

namespace App\Model\User;

use App\Model\BaseModel;
use App\Model\Payment\PaymentChannelModel;
use App\Model\Payment\PaymentTypeModel;

/**
 * Class UserVipOrderModel
 * @package App\Model\User
 * @property $orderId int | 订单id
 * @property $orderNo string | 订单号
 * @property $userId int | 用户id
 * @property $pageId int | 订单来源页面id
 * @property $channelId int | 订单来源渠道id
 * @property $paymentTypeId int | 支付类型id
 * @property $paymentChannelId int | 支付渠道id
 * @property $goodsId int | 商品id
 * @property $amount string | 商品价格
 * @property $status int | 状态
 * @property $createDate date | 创建日期
 * @property $createTime datetime | 创建时间
 * @property $finishDate date | 完成日期
 * @property $finishTime datetime | 完成时间
 * @property $updateTime datetime | 更新时间
 */
class UserVipOrderModel extends BaseModel
{
    protected $tableName = 'user_vip_order';

    protected $primaryKey = 'orderId';
    protected $createTime = 'createTime';
    protected $autoTimeStamp = 'datetime';
    protected $updateTime = 'updateTime';

    const STATE_WAITING_BUYERS_PAYMENT = 'WaitingBuyersPayment';
    const STATE_BUYER_CANCELS_PAYMENT = 'BuyerCancelsPayment';
    const STATE_BUYER_PAYMENT_TIMEOUT = 'BuyerPaymentTimeout';
    const STATE_ORDER_COMPLETED = 'OrderCompleted';

    const STATUS_NAME_KEY_LIST = [
        ['key' => self::STATE_WAITING_BUYERS_PAYMENT, 'name' => '等待买家付款'],
        ['key' => self::STATE_BUYER_CANCELS_PAYMENT, 'name' => '买家取消付款'],
        ['key' => self::STATE_BUYER_PAYMENT_TIMEOUT, 'name' => '买家付款超时'],
        ['key' => self::STATE_ORDER_COMPLETED, 'name' => '订单完成'],
    ];

    const STATUS_NAME_LIST = [
        self::STATE_WAITING_BUYERS_PAYMENT => '等待买家付款',
        self::STATE_BUYER_CANCELS_PAYMENT => '买家取消付款',
        self::STATE_BUYER_PAYMENT_TIMEOUT => '买家付款超时',
        self::STATE_ORDER_COMPLETED => '订单完成',
    ];

    public function goodsRelation()
    {
        return $this->hasOne(UserVipGoodsModel::class, null, 'goodsId', 'goodsId');
    }

    public function paymentTypeRelation()
    {
        return $this->hasOne(PaymentTypeModel::class, null, 'paymentTypeId', 'paymentTypeId');
    }

    public function paymentChannelRelation()
    {
        return $this->hasOne(PaymentChannelModel::class, null, 'paymentChannelId', 'paymentChannelId');
    }

    /**
     * 生成编号
     * @return $this
     */
    public function generateNumber($prefix = 'VIP')
    {
        $this->orderNo = $prefix . date('Ymd') . date('His') . substr(str_pad($this->orderId, 4, 0, STR_PAD_LEFT), -4);
        return $this;
    }

    public function save()
    {
        $id = parent::save();
        if (!$id) {
            return false;
        }

        $result = $this->generateNumber()->update();
        if (!$result) {
            return false;
        }

        return $id;
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['orderId']) && $keyword['orderId']) {
            if (is_array($keyword['orderId'])) {
                $where['orderId'] = [$keyword['orderId'], 'IN'];
            } else {
                $where['orderId'] = $keyword['orderId'];
            }
        }

        isset($keyword['userId']) && $keyword['userId'] && $where['userId'] = $keyword['userId'];
        isset($keyword['orderNo']) && $keyword['orderNo'] && $where['orderNo'] = $keyword['orderNo'];

        isset($keyword['pageId']) && $keyword['pageId'] && $where['pageId'] = is_array($keyword['pageId']) ? [$keyword['pageId'], 'IN'] : $keyword['pageId'];
        isset($keyword['channelId']) && $keyword['channelId'] && $where['channelId'] = is_array($keyword['channelId']) ? [$keyword['channelId'], 'IN'] : $keyword['channelId'];

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['dateStart'])) {
            $this->where('createDate', $keyword['dateStart'], '>=');
        }
        if (isset($keyword['dateEnd'])) {
            $this->where('createDate', $keyword['dateEnd'], '<=');
        }

        isset($keyword['status']) && $keyword['status'] && $where['status'] = $keyword['status'];

        return $where;
    }

    ##### 获取分组支付统计 begin #####

    public function getGroupSum($keyword, $sourceFiled)
    {
        $where = $this->parseKeywordToWhere($keyword);

        // 这里如果筛选了渠道id其实也没有加having语句，知道就行了。不影响使用。
        return $this
            ->field(["CONCAT(createDate,'_',{$sourceFiled}) AS dateKey", 'COUNT(orderId) AS orderCount', 'IFNULL(SUM(amount), 0) AS amount'])
            ->where($where)
            ->where([
                $sourceFiled => [0, '>'],
                'status' => self::STATE_ORDER_COMPLETED,
            ])
            ->group("createDate,{$sourceFiled}")
            ->indexBy('dateKey');
    }

    public function getSum($keyword, $sourceFiled)
    {
        $where = $this->parseKeywordToWhere($keyword);

        if (!isset($where[$sourceFiled])) {
            $where[$sourceFiled] = [0, '>'];
        }

        return $this
            ->field(['COUNT(orderId) AS orderCount', 'IFNULL(SUM(amount), 0) AS amount'])
            ->where($where)
            ->where([
                'status' => self::STATE_ORDER_COMPLETED,
            ])
            ->get();
    }
    ##### 获取分组支付统计 end #####

    ##### 获取支付人数统计 begin #####

    public function getGroupUserCount($keyword, $sourceFiled)
    {
        $where = $this->parseKeywordToWhere($keyword);

        // 这里如果筛选了渠道id其实也没有加having语句，知道就行了。不影响使用。
        return $this
            ->field(["CONCAT(createDate,'_',{$sourceFiled}) AS dateKey", 'COUNT(userId) AS userCount'])
            ->where($where)
            ->where([
                $sourceFiled => [0, '>'],
            ])
            ->group("createDate,{$sourceFiled}")
            ->indexBy('dateKey');
    }

    public function getUserCount($keyword, $sourceFiled)
    {
        $where = $this->parseKeywordToWhere($keyword);
        return $this
            ->field(['COUNT(userId) AS userCount'])
            ->where($where)
            ->where([
                $sourceFiled => [0, '>'],
            ])
            ->get();
    }

    ##### 获取支付人数统计 end #####
}