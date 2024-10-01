<?php

namespace App\Service\Oss\Aws;

use App\Enum\ConfigKey\OssConfigKey;
use App\Enum\Upload;
use App\Model\Common\ConfigModel;
use App\Service\Oss\AwsOssService;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Utility\File;
use Exception;

class EncryptionRemoteImage
{
    use Singleton;

    public function move($target, $type = '',$fileType = '', $date = '')
    {
        print_r([$target,$type,$fileType,$date]);
        $filenameSlice = explode('.', $target);
        $suffix = end($filenameSlice);
        $fullTempFileName = Func::getPublicPath() . '/temp/' . uniqid() . '.' . $suffix;
        $encryptionFullTempFileName = Func::getPublicPath() . '/temp/' . uniqid() . '.xyz';

        if($fileType == 'url'){
            File::createFile($fullTempFileName, '');
            $result = $this->downloadRemoteImage($target,$fullTempFileName);
            if (!$result) {
                unlink($fullTempFileName);
                throw new Exception('下载远程文件失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
        }elseif($fileType == 'awsS3'){
            $awsUrl = ConfigModel::create()->getConfigValue(OssConfigKey::AWS_S3_HOST).$target;
            File::createFile($fullTempFileName, '');
            $result = $this->downloadRemoteImage($awsUrl,$fullTempFileName);
            if (!$result) {
                unlink($fullTempFileName);
                throw new Exception('下载远程文件失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }
        }else{
            $fullTempFileName = Func::getPublicPath() . $target;
        }

        // 读取原始图片的字节流
        $imageData = file_get_contents($fullTempFileName);

        // 在字节流前面添加自定义内容
        $modifiedImageData = mt_rand(100, 999) . $imageData;

        // 将修改后的字节流保存为文件
        file_put_contents($encryptionFullTempFileName, $modifiedImageData);

        $mimetype = Func::getMimetype($fullTempFileName);

        $path = Upload::getImageDatePath($type, $date);

        $fileName = Func::CreateGuid() . '.' . 'xyz';
        $key = $path . DIRECTORY_SEPARATOR . $fileName;
        $body = fopen($encryptionFullTempFileName, 'r+');
        $result = AwsOssService::getInstance()->putImage($key, $body, $mimetype);

        fclose($body);
        if($fileType != 'up'){
            unlink($fullTempFileName);
        }
        unlink($encryptionFullTempFileName);

        return $result;
    }

    public function downloadRemoteImage($imageUrl, $savePath)
    {
        // 创建 cURL 资源
        $curl = curl_init($imageUrl);

        // 设置 cURL 选项
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true); // 启用 SSL 证书验证
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查 SSL 证书主机名

        // 执行请求并获取响应
        $response = curl_exec($curl);

        // 检查请求是否成功
        if ($response === false) {
            $error = curl_error($curl);
            // 处理请求错误
            echo "cURL Error: $error\n";
            curl_close($curl);
            return false;
        } else {
            // 将响应保存为文件
            if (file_put_contents($savePath, $response) !== false) {
                echo "Image downloaded successfully!\n";
                curl_close($curl);
                return true;
            } else {
                echo "Failed to save image!\n";
                curl_close($curl);
                return false;
            }
        }
    }

}