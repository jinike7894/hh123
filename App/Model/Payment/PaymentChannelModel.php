<?php

namespace App\Model\Payment;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class PaymentChannelModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'payment_channel';

    protected $primaryKey = 'paymentChannelId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function typeRelation()
    {
        return $this->hasOne(PaymentTypeModel::class, null, 'paymentTypeId', 'paymentTypeId');
    }

    public function platformRelation()
    {
        return $this->hasOne(PaymentPlatformModel::class, null, 'paymentPlatformId', 'paymentPlatformId');
    }

    /**
     * 获取可用的渠道列表
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getAvailableList()
    {
        $data = PaymentTypeModel::create()->field(['paymentTypeId', 'typeName', 'typeKey'])
            ->with('availableChannelsRelation')
            ->where(['status' => PaymentTypeModel::STATE_NORMAL])
            ->order('sort', 'ASC')
            ->all();

        $result = [];
        foreach ($data as $datum) {
            $temp = $datum->toArray();
            $temp['availableChannelsRelation'] = $datum['availableChannelsRelation'];
            $result[] = $temp;
        }

        return $result;
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['paymentChannelId']) && $keyword['paymentChannelId'] && $where['paymentChannelId'] = $keyword['paymentChannelId'];
        isset($keyword['paymentPlatformId']) && $keyword['paymentPlatformId'] && $where['paymentPlatformId'] = $keyword['paymentPlatformId'];
        isset($keyword['paymentTypeId']) && $keyword['paymentTypeId'] && $where['paymentTypeId'] = $keyword['paymentTypeId'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }
}