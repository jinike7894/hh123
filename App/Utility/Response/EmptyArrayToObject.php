<?php

namespace App\Utility\Response;

class EmptyArrayToObject extends CommonResponse
{

    public function exec($data)
    {
        $jsonString = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return str_replace('"extension":[]', '"extension":{}', $jsonString);
    }
}