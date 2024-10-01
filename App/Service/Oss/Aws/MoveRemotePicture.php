<?php

namespace App\Service\Oss\Aws;

use App\Enum\Upload;
use App\Service\Oss\AwsOssService;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Utility\File;
use Exception;

class MoveRemotePicture
{
    use Singleton;

    public function move($target, $type = '', $date = '')
    {
        $filenameSlice = explode('.', $target);
        $suffix = end($filenameSlice);
        $fullTempFileName = Func::getPublicPath() . '/temp/' . uniqid() . '.' . $suffix;

        $httpClient = new HttpClient($target);
        File::createFile($fullTempFileName, '');
        $result = $httpClient->download($fullTempFileName);
        if (!$result) {
            unlink($fullTempFileName);
            throw new Exception('下载远程文件失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        $mimetype = Func::getMimetype($fullTempFileName);

        $path = Upload::getImageDatePath($type, $date);

        $fileName = Func::CreateGuid() . '.' . $suffix;
        $key = $path . DIRECTORY_SEPARATOR . $fileName;
        $body = fopen($fullTempFileName, 'r+');

        $result = AwsOssService::getInstance()->putImage($key, $body, $mimetype);

        fclose($body);
        unlink($fullTempFileName);

        return $result;
    }
}