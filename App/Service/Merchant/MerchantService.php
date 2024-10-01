<?php

namespace App\Service\Merchant;

use App\Enum\BalanceChangeType;
use App\Enum\UserType;
use App\Model\Admin\AdminModel;
use App\Model\Admin\RoleModel;
use App\Model\Merchant\MerchantClickBillModel;
use App\Model\Merchant\MerchantModel;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\Utility\Hash;
use Exception;
use Throwable;

class MerchantService
{
    use Singleton;

    public function addMerchant($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();
            $merchant = MerchantModel::create()
                ->where([
                    'merchantName' => $data['merchantName'],
                    'status' => [MerchantModel::STATE_DELETED, '>'],
                ])
                ->get();
            if ($merchant) {
                throw new Exception('该商户已存在', Status::CODE_BAD_REQUEST);
            }

            $merchant = MerchantModel::create($data);
            $result = $merchant->save();

            if (!$result) {
                throw new Exception('商户创建失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $admin = AdminModel::create()
                ->where([
                    'adminAccount' => $merchant->merchantName,
                    'status' => [AdminModel::STATE_DELETED, '>'],
                ])
                ->get();
            if ($admin) {
                throw new Exception('管理员账号已存在，请更换。', Status::CODE_BAD_REQUEST);
            }

            // 这里同步创建商户对应的后台账号
            $result = AdminModel::create([
                'roleId' => RoleModel::ID_MERCHANT,
                'merchantId' => $merchant->merchantId,
                'adminNickname' => $merchant->merchantName,
                'adminAccount' => $merchant->merchantName,
                'adminPassword' => Hash::makePasswordHash(AdminModel::DEFAULT_PASSWORD),
                'adminType' => UserType::TYPE_MERCHANT,
            ])->save();

            if (!$result) {
                throw new Exception('商户后台账号创建失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $merchant->merchantId;
    }

    /**
     * 如果不存在则添加商户
     * @param $merchantName
     * @return MerchantModel|array|bool|\EasySwoole\ORM\AbstractModel|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|null
     * @throws Throwable
     */
    public function addMerchantIfNotExists($merchantName)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $merchant = MerchantModel::create()
                ->get([
                    'merchantName' => $merchantName,
                    'status' => [MerchantModel::STATE_DELETED, '>'],
                ]);
            if (!$merchant) {
                $merchant = MerchantModel::create([
                    'merchantName' => $merchantName,
                    'status' => MerchantModel::STATE_NORMAL,
                ]);
                $result = $merchant->save();

                if (!$result) {
                    throw new Exception('商户创建失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $merchant;
    }

    /**
     * 如果不存在则添加管理员
     * @param $merchantName
     * @return MerchantModel|array|bool|\EasySwoole\ORM\AbstractModel|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|null
     * @throws Throwable
     */
    public function addAdminIfNotExists($merchantName)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $merchant = MerchantModel::create()
                ->get([
                    'merchantName' => $merchantName,
                    'status' => [MerchantModel::STATE_DELETED, '>'],
                ]);
            if($merchant){
                // 如果管理员不在自动创建管理员
                $admin = AdminModel::create()
                    ->where([
                        'adminAccount' => $merchant->merchantName,
                        'status' => [AdminModel::STATE_DELETED, '>'],
                    ])
                    ->get();
                if (!$admin) {
                    // 这里同步创建商户对应的后台账号
                    AdminModel::create([
                        'roleId' => RoleModel::ID_MERCHANT,
                        'merchantId' => $merchant->merchantId,
                        'adminNickname' => $merchant->merchantName,
                        'adminAccount' => $merchant->merchantName,
                        'adminPassword' => Hash::makePasswordHash(AdminModel::DEFAULT_PASSWORD),
                        'adminType' => UserType::TYPE_MERCHANT,
                    ])->save();
                }
            }
            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }
        return $merchant;
    }

    /**
     * 获取商户还未结算的总账单金额
     * @param $merchantId
     * @return int|mixed
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getUnsettledAmount($merchantId)
    {
        $data = MerchantClickBillModel::create()
            ->where([
                'merchantId' => $merchantId,
                'settlement' => MerchantClickBillModel::SETTLE_PENDING,
            ])
            ->sum('amount');

        return $data ?: 0;
    }

    /**
     * 记录账单
     * @param $merchantId
     * @param $date
     * @param $cost
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function addUnsettledBill($merchantId, $date, $cost)
    {
        return MerchantClickBillModel::create()
            ->data([
                'merchantId' => $merchantId,
                'date' => $date,
                'count' => 1,
                'amount' => $cost,
                'settlement' => MerchantClickBillModel::SETTLE_PENDING,
            ])
            ->duplicate([
                'count' => QueryBuilder::inc(),
                'amount' => QueryBuilder::inc($cost),
            ])
            ->save();
    }

    /**
     * 获取未结算的账单
     * @param $date
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getUnsettledBillList($date)
    {
        return MerchantClickBillModel::create()
            ->field(['merchantId', 'date', 'count', 'amount'])
            ->where([
                'date' => [$date, '<='],
                'settlement' => MerchantClickBillModel::SETTLE_PENDING,
            ])
            ->all();
    }

    /**
     * 结算账单
     * @param MerchantClickBillModel $bill
     * @return \App\Model\Merchant\MerchantBalanceChangeModel
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function settleBill(MerchantClickBillModel $bill)
    {
        /**
         * @var $merchant MerchantModel
         */
        $merchant = Func::getWho($bill['merchantId'], UserType::TYPE_MERCHANT);

        $change = $merchant->updateBalance($bill['amount'], BalanceChangeType::TYPE_CLICK, '自动结算账单');

        $bill->update(['settlement' => MerchantClickBillModel::SETTLE_COMPLETED]);

        return $change;
    }
}