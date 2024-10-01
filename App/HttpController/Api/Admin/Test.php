<?php

namespace App\HttpController\Api\Admin;

class Test extends AdminBase
{
    public function index()
    {
        $this->writeJson(200, [], '权限验证成功');
    }
}