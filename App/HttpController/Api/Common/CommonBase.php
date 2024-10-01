<?php

namespace App\HttpController\Api\Common;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\ApiBase;
use App\Model\Common\ConfigModel;
use EasySwoole\Http\Message\Status;

class CommonBase extends ApiBase
{

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws \Throwable
     */
    function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }

        // 判断是否开启维护
        $maintain = ConfigModel::create()->getConfigValue(SystemConfigKey::WEBSITE_MAINTENANCE);
        $maintain = json_decode($maintain, true);
        if ($maintain['status'] == 1) {
            throw new \Exception($maintain['content'], Status::CODE_BAD_REQUEST);
        }

        return true;
    }

}