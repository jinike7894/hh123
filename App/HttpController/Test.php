<?php

namespace App\HttpController;

use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\Message\Status;

class Test extends BaseController
{
    public function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }

        if (Core::getInstance()->runMode() != 'dev') {
            return false;
        }

        return true;
    }

    public function index()
    {
        $data = 1;
        $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function server()
    {
        $this->writeJson(Status::CODE_OK, $_SERVER, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function ip()
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader('x-real-ip');
        $xff = $this->request()->getHeader('x-forwarded-for');

        $this->writeJson(Status::CODE_OK, [
            'remote_ip' => $clientAddress,
            'xri' => $xri,
            'xff' => $xff,
            'result' => $this->clientRealIP(),
        ], Status::getReasonPhrase(Status::CODE_OK));
    }

    public function header()
    {
        $this->writeJson(Status::CODE_OK, $this->request()->getHeaders(), Status::getReasonPhrase(Status::CODE_OK));
    }

    public function runMode()
    {
        $this->writeJson(Status::CODE_OK, Core::getInstance()->runMode(), Status::getReasonPhrase(Status::CODE_OK));
    }

    public function code200()
    {
        $result = ['code' => Status::CODE_OK];
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function code400()
    {
        $result = ['code' => Status::CODE_BAD_REQUEST];
        return $this->writeJson(Status::CODE_BAD_REQUEST, $result, Status::getReasonPhrase(Status::CODE_BAD_REQUEST));
    }

    public function code401()
    {
        $result = ['code' => Status::CODE_UNAUTHORIZED];
        return $this->writeJson(Status::CODE_UNAUTHORIZED, $result, Status::getReasonPhrase(Status::CODE_UNAUTHORIZED));
    }

    public function code403()
    {
        $result = ['code' => Status::CODE_FORBIDDEN];
        return $this->writeJson(Status::CODE_FORBIDDEN, $result, Status::getReasonPhrase(Status::CODE_FORBIDDEN));
    }

    public function code500()
    {
        $result = ['code' => Status::CODE_INTERNAL_SERVER_ERROR];
        return $this->writeJson(Status::CODE_INTERNAL_SERVER_ERROR, $result, Status::getReasonPhrase(Status::CODE_INTERNAL_SERVER_ERROR));
    }
}