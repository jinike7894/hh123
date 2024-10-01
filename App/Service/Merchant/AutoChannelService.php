<?php

namespace App\Service\Merchant;

use App\Model\Merchant\ChannelModel;
use App\Model\Navigation\PageModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class AutoChannelService
{
    use Singleton;

    public function autoCreatePageAndChannel($key)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            // 因为页面是真删除，所有可以不带状态
            $page = PageModel::create()->get(['pageName' => $key]);

            if (!$page) {
                $indexPage = PageModel::create()->get(['pageName' => 'index.html']);

                $page = PageModel::create([
                    'pageName' => $key,
                    'pageTemplateId' => $indexPage->pageTemplateId,
                    'code' => '<script>console.log(\'test\')</script>',
                    'navCode' => '<script>console.log(\'test\')</script>',
                    'description' => '自动创建页面',
                    'statisticEnabled' => 0,
                    'statisticConfig' => '',
                    'ipCost' => 0,
                    'status' => PageModel::STATE_NORMAL,
                ]);

                $result = $page->save();
                if ($result === false) {
                    throw new Exception('页面自动创建失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
            }

            // 渠道是假删除，所以带状态
            $channel = ChannelModel::create()
                ->get([
                    'channelKey' => $key,
                    'status' => [ChannelModel::STATE_DELETED, '>'],
                ]);

            if (!$channel) {
                // 需要同时检查商户是否存在
                $merchant = MerchantService::getInstance()->addMerchantIfNotExists($key);

                $channel = ChannelModel::create([
                    'merchantId' => $merchant->merchantId,
                    'channelKey' => $key,
                    'remark' => '自动创建渠道',
                    'status' => ChannelModel::STATE_NORMAL,
                ]);

                $result = $channel->save();
                if ($result === false) {
                    throw new Exception('渠道自动创建失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
            }
            MerchantService::getInstance()->addAdminIfNotExists($key);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return [$page, $channel];
    }
}