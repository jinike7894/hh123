<?php

namespace App\Model\Merchant;

use App\Enum\BalanceChangeType;
use App\Model\BaseModel;

/**
 * Class MerchantBalanceChangeModel
 * @package App\Model\Merchant
 * @property $merchantBalanceChangeId int | id
 * @property $merchantId int | 商户id
 * @property $type enum | 账变类型 1.调整加币 + ManualAdd 2.调整减币 - ManualReduce 3.点击计费 - Click
 * @property $amount float | 此次帐变金额
 * @property $preBalance float | 此次帐变前金额
 * @property $newBalance float | 此次帐变后金额
 * @property $remark string | 备注
 * @property $createDate date | 创建日期
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class MerchantBalanceChangeModel extends BaseModel
{
    protected $tableName = 'merchant_balance_change';

    protected $primaryKey = 'merchantBalanceChangeId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        // 没有获取model是否设置alias的接口，这里只能带前缀和不带前缀的分开写。
        isset($keyword['merchantId']) && $where['merchantId'] = $keyword['merchantId'];
        isset($keyword['mbc.merchantId']) && $where['mbc.merchantId'] = $keyword['mbc.merchantId'];

        // 同字段的只能分开设置where，返回不了一个数组。
        if (isset($keyword['createDateStart'])) {
            $this->where('createDate', $keyword['createDateStart'], '>=');
        }
        if (isset($keyword['createDateEnd'])) {
            $this->where('createDate', $keyword['createDateEnd'], '<=');
        }

        if (isset($keyword['type'])) {
            if (!is_array($keyword['type'])) {
                $keyword['type'] = explode(',', $keyword['type']);
            }

            $temp = array_intersect($keyword['type'], BalanceChangeType::TYPE_ALL);
            switch (true) {
                case count($temp) > 1:
                    $where['type'] = [$temp, 'IN'];
                    break;
                case count($temp) == 1:
                    $where['type'] = current($temp);
                    break;
            }
        }

        return $where;
    }
}