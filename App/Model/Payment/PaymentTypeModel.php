<?php

namespace App\Model\Payment;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class PaymentTypeModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'payment_type';

    protected $primaryKey = 'paymentTypeId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function availableChannelsRelation()
    {
        return $this->hasMany(PaymentChannelModel::class, function (QueryBuilder $query) {
            $pc = PaymentChannelModel::create()->getTableName();

            $query
                ->fields(['paymentChannelId', 'paymentTypeId', 'channelName', 'channelAlias', 'min', 'max', "{$pc}.status"])
                ->join(PaymentPlatformModel::create()->getTableName() . ' AS pp', "pp.paymentPlatformId={$pc}.paymentPlatformId", 'LEFT')
                ->where('pp.status', PaymentChannelModel::STATE_NORMAL)
                ->where($pc . '.status', PaymentChannelModel::STATE_NORMAL)
                ->orderBy('sort', 'ASC');
            return $query;
        }, 'paymentTypeId', 'paymentTypeId');
    }

}