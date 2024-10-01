<?php

namespace App\Service\Oss\Aws;

use App\Enum\Upload;
use App\Service\Oss\AwsOssService;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use Exception;

class MoveLocalPicture
{
    use Singleton;

    public function move($target, $type = '', $date = '')
    {
        $filenameSlice = explode('.', $target);
        $suffix = end($filenameSlice);

        if (!is_file($target)) {
            throw new Exception('未找到目标文件', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        $mimetype = Func::getMimetype($target);

        $path = Upload::getImageDatePath($type, $date);

        $fileName = Func::CreateGuid() . '.' . $suffix;
        $key = $path . DIRECTORY_SEPARATOR . $fileName;
        $body = fopen($target, 'r+');

        $result = AwsOssService::getInstance()->putImage($key, $body, $mimetype);

        fclose($body);
        unlink($target);

        return $result;
    }
}