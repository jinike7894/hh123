<?php

namespace App\HttpController\Api;

use App\HttpController\BaseController;
use App\Utility\LogHandler;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\Exception\Annotation\ParamValidateError;

abstract class ApiBase extends BaseController
{
    public function index()
    {
        $this->actionNotFound('index');
    }

    protected function actionNotFound(?string $action): void
    {
        $this->writeJson(Status::CODE_NOT_FOUND);
    }

    public function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }
        return true;
    }

    protected function onException(\Throwable $throwable): void
    {
        if ($throwable instanceof ParamValidateError) {
            $msg = $throwable->getValidate()->getError()->getErrorRuleMsg();
            $this->writeJson(Status::CODE_BAD_REQUEST, [], $msg);
        } else {
            if (Core::getInstance()->runMode() == 'dev') {
                $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, [], $throwable->getMessage());
            } else {
                Trigger::getInstance()->throwable($throwable);
                LogHandler::getInstance()->log($throwable->getMessage(), LogHandler::LOG_LEVEL_ERROR, 'ApiBase error');
                $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, [], '系统内部错误，请稍后重试');
            }
        }
    }
}