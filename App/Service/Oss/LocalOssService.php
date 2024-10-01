<?php

namespace App\Service\Oss;

use App\Enum\Upload;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Message\UploadFile;
use EasySwoole\Http\Request;
use EasySwoole\Utility\File;
use Exception;

class LocalOssService
{
    use Singleton;

    /**
     * 上传图片
     * @param Request $request
     * @param string $type 业务类型
     * @param string $formKey
     * @return array
     * @throws Exception
     */
    public function uploadImage(Request $request, string $type, string $formKey = 'file'): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $request->getUploadedFile($formKey);
        if (!$uploadFile) {
            throw new Exception('请选择上传的文件！', Status::CODE_BAD_REQUEST);
        }

        $mediaType = explode('/', $uploadFile->getClientMediaType());
        $mediaType = $mediaType[1] ?? '';
        if (!in_array($mediaType, ['png', 'jpg', 'gif', 'jpeg', 'webp', 'm3u8'])) {
            throw new Exception('文件类型不正确！类型：' . $mediaType, Status::CODE_BAD_REQUEST);
        }

        $path = Upload::getImageDatePath($type);

        // dirPath 是要上传到Public目录，然后域名访问的时候是不加Public的，这个要注意一下。
        $dirPath = Func::getPublicPath() . DIRECTORY_SEPARATOR . $path;
        // 如果要防止图片被抓的话，这里可以改为其他后缀。
        $appFileName = Func::CreateGuid() . '.' . $mediaType;
        //$fileName = Func::CreateGuid() . '.' . 'xyz';

        File::createDirectory($dirPath);
        $uploadFile->moveTo($dirPath . DIRECTORY_SEPARATOR . $appFileName);


        return [
            'path' => DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $appFileName,
        ];
    }
    public function uploadFile(Request $request, string $type, string $formKey = 'file'): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $request->getUploadedFile($formKey);
        if (!$uploadFile) {
            throw new Exception('请选择上传的文件！', Status::CODE_BAD_REQUEST);
        }

        $mediaType = explode('/', $uploadFile->getClientMediaType());
        $mediaType = $mediaType[1] ?? '';
        if (!in_array($mediaType, ['png', 'jpg', 'gif', 'jpeg', 'webp', 'm3u8'])) {
            throw new Exception('文件类型不正确！类型：' . $mediaType, Status::CODE_BAD_REQUEST);
        }
        if($uploadFile->getSize()>5*1024*1024){
            throw new Exception('上传图片最大不能超过5m', Status::CODE_BAD_REQUEST);
        }
        $path = Upload::getImageDatePath($type);

        // dirPath 是要上传到Public目录，然后域名访问的时候是不加Public的，这个要注意一下。
        $dirPath = Func::getPublicPath() . DIRECTORY_SEPARATOR . $path;
        // 如果要防止图片被抓的话，这里可以改为其他后缀。
        $appFileName = Func::CreateGuid() . '.' . $mediaType;
        //$fileName = Func::CreateGuid() . '.' . 'xyz';

        File::createDirectory($dirPath);
        $uploadFile->moveTo($dirPath . DIRECTORY_SEPARATOR . $appFileName);


        return [
            'path' => DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $appFileName,
        ];
    }
    /**
     * 删除文件
     * @param string $path 项目Public目录开始的相对路径
     * @return bool
     */
    public function deleteObject($path)
    {
        $filePath = Func::getPublicPath() . $path;
        if (is_file($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}