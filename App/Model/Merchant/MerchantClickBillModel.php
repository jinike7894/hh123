<?php

namespace App\Model\Merchant;

use App\Model\BaseModel;

/**
 * Class MerchantClickBillModel
 * @package App\Model\Merchant
 * @property $merchantId int | 商户id
 * @property $date string | 日期
 * @property $count int | 总计数（笔数）
 * @property $amount float | 此次帐变金额
 * @property $settlement enum | 结算状态 1.Completed 已完成的 2.Pending 待处理的
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class MerchantClickBillModel extends BaseModel
{
    protected $tableName = 'merchant_click_bill';

    protected $primaryKey = ['merchantId', 'date'];
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const SETTLE_COMPLETED = 'Completed';
    const SETTLE_PENDING = 'Pending';

    /**
     * 获取一组商户的未结算账单金额
     * @param array|int|null $merchantId
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getUnsettledAmount($merchantId = null)
    {
        if ($merchantId) {
            if (is_array($merchantId)) {
                $this->where(['merchantId' => [$merchantId, 'IN']]);
            }

            if (is_numeric($merchantId)) {
                $this->where(['merchantId' => $merchantId]);
            }
        }

        return $this
            ->field([
                'merchantId',
                'SUM(amount) AS amount',
            ])
            ->where(['settlement' => self::SETTLE_PENDING])
            ->group('merchantId')
            ->indexBy('merchantId');
    }
}