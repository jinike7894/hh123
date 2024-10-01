<?php

namespace App\Model\Merchant;

use App\Component\CommonStatusInterface;
use App\Enum\BalanceChangeType;
use App\Model\BaseModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * Class MerchantModel
 * @package App\Model\Merchant
 * @property $merchantId int | id
 * @property $merchantName string | 商户名称
 * @property $balance float | 余额
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class MerchantModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'merchant';

    protected $primaryKey = 'merchantId';
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

        if (isset($keyword['merchantName']) && strlen($keyword['merchantName']) > 0) {
            $where['merchantName'] = ['%' . $keyword['merchantName'] . '%', 'LIKE'];
        }

        return $where;
    }

    /**
     * 更新余额（包括写变化记录）
     * 1.每一个case仅需填写对应的 type amount frozenAmount
     * 2.设置新的余额和冻结余额
     * 3.分别更新余额和记录金额变化记录
     * @param float $amount 变化金额
     * @param string $type BalanceChangeType
     * @param string $remark 备注
     * @return MerchantBalanceChangeModel
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws Throwable
     */
    public function updateBalance(float $amount, string $type, string $remark = '')
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            // 统一取绝对值，用类型决定是加还是减。
            $amount = abs($amount);

            $balanceChange = MerchantBalanceChangeModel::create([
                'merchantId' => $this->merchantId,
                'preBalance' => $this->balance,
            ]);

            $update = [];

            switch ($type) {
                case BalanceChangeType::TYPE_MANUAL_ADD:

                    $balanceChange->type = BalanceChangeType::TYPE_MANUAL_ADD;
                    $balanceChange->amount = $amount;

                    $update['balance'] = QueryBuilder::inc($amount);
                    break;
                case BalanceChangeType::TYPE_MANUAL_REDUCE:

                    if ($this->balance < $amount) {
                        throw new Exception('钱包余额不足', Status::CODE_BAD_REQUEST);
                    }

                    $balanceChange->type = BalanceChangeType::TYPE_MANUAL_REDUCE;
                    $balanceChange->amount = -$amount;

                    $update['balance'] = QueryBuilder::dec($amount);
                    break;
                case BalanceChangeType::TYPE_CLICK:

                    if ($this->balance < $amount) {
                        throw new Exception('钱包余额不足', Status::CODE_BAD_REQUEST);
                    }

                    $balanceChange->type = BalanceChangeType::TYPE_CLICK;
                    $balanceChange->amount = -$amount;

                    $update['balance'] = QueryBuilder::dec($amount);
                    break;
            }
            $result = $this->update($update);

            if (!$result) {
                throw new Exception('更新余额信息失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $balanceChange->newBalance = $this->balance;
            $balanceChange->remark = $remark;
            $now = time();
            $balanceChange->createDate = date('Y-m-d', $now);
            $balanceChange->createTime = date('Y-m-d H:i:s', $now);
            $result = $balanceChange->save();
            if (!$result) {
                throw new Exception('保存余额变化信息失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $balanceChange;
    }
}