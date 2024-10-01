<?php

namespace App\Utility\Response;

use EasySwoole\Component\Singleton;

class CommonResponse
{
    use Singleton;

    public function exec($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}