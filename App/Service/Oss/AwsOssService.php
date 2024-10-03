<?php

namespace App\Service\Oss;

use App\Enum\ConfigKey\OssConfigKey;
use App\Enum\Upload;
use App\Model\Common\ConfigModel;
use App\Utility\Func;
use Aws\S3\S3Client;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\Http\Message\UploadFile;
use EasySwoole\Http\Request;
use Exception;

class AwsOssService
{
    use Singleton;

    public $aws = null;
    public $s3Client = null;
    public $s3Config = [];

    public function __construct()
    {
        $this->s3Config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_OSS);

        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->s3Config[OssConfigKey::AWS_S3_REGION],
            'endpoint' => $this->s3Config[OssConfigKey::AWS_S3_ENDPOINT],
            'credentials' => [
                'key' => $this->s3Config[OssConfigKey::AWS_S3_ACCESS_ID],
                'secret' => $this->s3Config[OssConfigKey::AWS_S3_ACCESS_KEY],
            ],
        ]);
    }

    /**
     * 获取域名，如果是手动拼接的情况下是这个。目前还有一个AWS_S3_HOST的配置。
     * @param string $bucket
     * @return array|string|string[]
     */
    public function getHost($bucket = '')
    {
        $bucket || $bucket = $this->s3Config[OssConfigKey::AWS_S3_BUCKET];
        return str_replace('https://', "https://{$bucket}.", $this->s3Config[OssConfigKey::AWS_S3_ENDPOINT]);
    }

    public function _example_getImage($key = '1.png')
    {
        try {
            $file = $this->s3Client->getObject([
                'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
                'Key' => $key,
            ]);
        } catch (Exception $e) {
            echo $e->getMessage();
        }

        $body = $file['Body']->getContents();
        return base64_encode($body);
    }

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
        if (!in_array($mediaType, ['png', 'jpg', 'gif', 'jpeg', 'webp', 'm3u8',"mp4"])) {
            throw new Exception('文件类型不正确！类型：' . $mediaType, Status::CODE_BAD_REQUEST);
        }
        if($uploadFile->getSize()>10*1024*1024){
            throw new Exception('上传图片最大不能超过10m', Status::CODE_BAD_REQUEST);
        }
        $path = Upload::getImageDatePath($type);
        // 如果要防止图片被抓的话，这里可以改为其他后缀。
        //$fileName = Func::CreateGuid() . '.' . $mediaType;
        $fileName = Func::CreateGuid() . '.' . 'xyz';
        $this->s3Client->putObject([
            'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
            'Key' => $path . DIRECTORY_SEPARATOR . $fileName,
            //'Key' => $fileName,
            'Body' => mt_rand(100, 999) . $uploadFile->getStream(), // 原生使用这个 fopen('/path/to/image.jpg', 'r'),
            'ContentType' => $uploadFile->getClientMediaType(), // 必须要加这个才能以图片返回。（否则是下载文件）
        ]);

        return [
            'path' => DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName,
            //'path' => DIRECTORY_SEPARATOR . $fileName,
        ];
    }
    //发帖上传图片
    public function uploadFile(Request $request, string $type, string $formKey = 'file'): array
    {
        /** @var UploadFile $uploadFile */
        $uploadFile = $request->getUploadedFile($formKey);
        if (!$uploadFile) {
            throw new Exception('请选择上传的文件！', Status::CODE_BAD_REQUEST);
        }

        $mediaType = explode('/', $uploadFile->getClientMediaType());
        $mediaType = $mediaType[1] ?? '';
        if (!in_array($mediaType, ['png', 'jpg', 'gif', 'jpeg', 'webp', 'm3u8',"mp4"])) {
            throw new Exception('文件类型不正确！类型：' . $mediaType, Status::CODE_BAD_REQUEST);
        }
        if($uploadFile->getSize()>10*1024*1024){
            throw new Exception('上传图片最大不能超过10m', Status::CODE_BAD_REQUEST);
        }
        $path = Upload::getImageDatePath($type);
        // 如果要防止图片被抓的话，这里可以改为其他后缀。
        //$fileName = Func::CreateGuid() . '.' . $mediaType;
        $fileName = Func::CreateGuid() . '.' . 'xyz';
        $this->s3Client->putObject([
            'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
            'Key' => $path . DIRECTORY_SEPARATOR . $fileName,
            //'Key' => $fileName,
            'Body' => mt_rand(100, 999) . $uploadFile->getStream(), // 原生使用这个 fopen('/path/to/image.jpg', 'r'),
            'ContentType' => $uploadFile->getClientMediaType(), // 必须要加这个才能以图片返回。（否则是下载文件）
        ]);

        return [
            'path' => DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName,
            //'path' => DIRECTORY_SEPARATOR . $fileName,
        ];
    }
    public function putImage($key, $body, $mimetype)
    {
        $this->s3Client->putObject([
            'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
            'Key' => $key,
            'Body' => $body,
            'ContentType' => $mimetype, // 必须要加这个才能以图片返回。（否则是下载文件）
        ]);

        return [
            'path' => DIRECTORY_SEPARATOR . $key,
        ];
    }

    /**
     * @param string $key 对象相对路径
     * @return bool
     * @throws Exception
     */
    public function deleteObject($key)
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
                'Key' => ltrim($key, '/'),
            ]);
        } catch (Exception $e) {
            throw new Exception('亚马逊S3删除对象失败', Status::CODE_INTERNAL_SERVER_ERROR);
        }

        return true;
    }
}