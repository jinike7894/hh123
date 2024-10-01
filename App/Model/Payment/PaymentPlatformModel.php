<?php

namespace App\Model\Payment;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class PaymentPlatformModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'payment_platform';

    protected $primaryKey = 'paymentPlatformId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }
}