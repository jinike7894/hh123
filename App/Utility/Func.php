<?php

namespace App\Utility;

use App\Enum\RedisDb;
use App\Enum\Upload;
use App\Enum\UserType;
use App\Model\Admin\AdminModel;
use App\Model\Merchant\MerchantModel;
use App\Model\User\UserModel;
use App\RedisKey\VerifyCode\VerifyCodeKey;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpClient\HttpClient;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\AbstractModel;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\File;
use EasySwoole\EasySwoole\Config;
use Exception;

class Func
{

    /**
     * 通过id和类型获取用户对象或商户对象
     * @param int $id
     * @param string $type
     * @return MerchantModel|AdminModel
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public static function getWho(int $id, string $type)
    {
        switch ($type) {
            case UserType::TYPE_MEMBER:
                $who = UserModel::create()->lockForUpdate()->get($id);
                break;
            case UserType::TYPE_MERCHANT:
                $who = MerchantModel::create()->lockForUpdate()->get($id);
                break;
            case UserType::TYPE_SYSTEM:
                $who = AdminModel::create()->lockForUpdate()->get($id);
                break;
            default:
                throw new \Exception('未知的用户类型', Status::CODE_BAD_REQUEST);
        }

        if (empty($who)) {
            throw new \Exception('未知的用户', Status::CODE_BAD_REQUEST);
        }

        return $who;
    }

    public static function httpsRequest($url, $data = [], $type = 'postJson', $responseType = 'getBody')
    {
        $headers = [
            'Content-Type: application/json;charset=utf-8',
            'Connection: Keep-Alive'
        ];

        $client = new HttpClient($url);
        $client->setTimeout(10);
        switch ($type) {
            case 'post':
                $response = $client->post($data, $headers);
                break;
            case 'postJson':
                $data = json_encode($data);
                $response = $client->postJson($data, $headers);
                break;
            case 'get':
                $response = $client->get($headers);
                break;
        }
        return $response->$responseType();
    }

    /**
     * 生成唯一字符串 32位/36位
     * @param string $delimiter
     * @return string
     */
    public static function CreateGuid($delimiter = '', $namespace = '')
    {
        static $guid = '';
        $uid = uniqid(mt_rand(), true);
        $data = $namespace;
        $data .= \EasySwoole\Utility\SnowFlake::make(1, 1);
        $hash = strtolower(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = substr($hash, 0, 8) .
            $delimiter .
            substr($hash, 8, 4) .
            $delimiter .
            substr($hash, 12, 4) .
            $delimiter .
            substr($hash, 16, 4) .
            $delimiter .
            substr($hash, 20, 12);
        return $guid;
    }

    /**
     * 获取excel单元格坐标
     * @param int $lineIndex 行号索引
     * @param int $charIndex 排行索引
     * @return string
     */
    public static function getExcelCellCoordinate(int $lineIndex, int $charIndex): string
    {
        // 处理字母部分
        ++$charIndex;
        $char = str_repeat('A', floor($charIndex / 26)) . chr(64 + $charIndex % 26);
        return $char . (++$lineIndex);
    }

    /**
     * 获取Excel组件配置
     * @return string[]
     */
    public static function getExcelConfig()
    {
        $downloadDir = Config::getInstance()->getConf('DOWNLOAD')['dir'] ?? 'Download' . DIRECTORY_SEPARATOR;
        return [
            'path' => EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . $downloadDir . date('Y') . DIRECTORY_SEPARATOR . date('m') . DIRECTORY_SEPARATOR . date('d') . DIRECTORY_SEPARATOR  // xlsx文件保存路径
        ];
    }

    /**
     * 导出xlsx
     * @param $headers
     * @param $list
     * @param $fileName
     * @return false|string
     * @throws Exception
     */
    public static function excel($headers, $list, $fileName)
    {
        if (!is_array($list) || !is_array($headers)) {
            return false;
        }

        $config = static::getExcelConfig();
        //创建文件夹
        File::createDirectory($config['path'], 0777);

        $excel = new \Vtiful\Kernel\Excel($config);

        // 处理xls需要的数据格式
        $data = [];
        if (!empty($list)) {
            foreach ($list as $row) {
                $temp = [];
                foreach ($headers as $value) {
                    $temp[] = $row[$value[1]] ?? '';
                }
                $data[] = $temp;
            }
        }

        $filePath = $excel
            ->fileName($fileName)
            ->header(array_column($headers, '0'))
            ->data($data)
            ->output();

        // 关闭当前打开的所有文件句柄 并 回收资源
        $excel->close();

        return $filePath;
    }

    /**
     * 验证图形验证码
     * @param $verifyUniqueId
     * @param $code
     * @param $codeType
     * @return bool
     * @throws Exception
     */
    public static function checkVerifyCodeFunc($verifyUniqueId, $code, $codeType): bool
    {
        if (!$verifyUniqueId || !$code || !$codeType) {
            throw new Exception('图形验证码错误', Status::CODE_BAD_REQUEST);
        }
        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        //后续验证
        $verifyCodeHash = $redis->get(VerifyCodeKey::imageCodeHash($codeType, $verifyUniqueId));
        $verifyCodeTime = $redis->get(VerifyCodeKey::imageCodeTime($codeType, $verifyUniqueId));
        if (!VerifyCodeTools::checkVerifyCode($code, $verifyCodeTime, $verifyCodeHash)) {
            throw new Exception('图形验证码错误', Status::CODE_BAD_REQUEST);
        }
        $redis->del(VerifyCodeKey::imageCodeHash($codeType, $verifyUniqueId));
        $redis->del(VerifyCodeKey::imageCodeTime($codeType, $verifyUniqueId));
        return true;
    }

    /**
     * 批量插入（请在外层调用处包裹事务）
     * eg: $result = Func::insertAll(UserModel::create(), $dataList);
     *
     * 注意，批量插入不会自动补齐时间字段，需要手动设置。
     * @param AbstractModel $model
     * @param array $dataList
     * @param array $option
     * @return bool
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Pool\Exception\PoolEmpty
     * @throws \Throwable
     */
    public static function insertAll(AbstractModel $model, array $dataList, $option = [])
    {
        $tableName = $model->getTableName();
        $limit = 500;

        $offset = 0;
        $dataSlice = array_slice($dataList, $offset, $limit);

        DbManager::getInstance()->startTransactionWithCount();

        while ($dataSlice) {
            $result = $model->func(function (QueryBuilder $builder) use ($tableName, $dataSlice, $option) {
                return $builder->insertAll($tableName, $dataSlice, $option);
            });

            if (!$result) {
                DbManager::getInstance()->rollbackWithCount();
                throw new Exception('批量插入数据失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $offset += $limit;
            $dataSlice = array_slice($dataList, $offset, $limit);
        };

        DbManager::getInstance()->commitWithCount();
        return true;
    }

    /**
     * 生成父子id树结构
     * @param $data
     * @param string $mine 主键名
     * @param string $parent 父键名
     * @param string $childField 孩子键名
     * @return array
     */
    public static function treeArray($data, $mine = 'id', $parent = 'parent_id', $childField = 'children')
    {
        // 方案1
        // 这种方法虽然效率高，但是有一个前提条件，就是需要按照正常顺序排序才能成功。
        // 如果子集在前则会失败
        /*
        $result = array();
        // 定义索引数组，用于记录节点在目标数组的位置，类似指针
        $p = array();

        foreach ($data as $val) {
            if ($val[$parent] == 0) {
                $i = count($result);
                $result[$i] = isset($p[$val[$mine]]) ? array_merge($val, $p[$val[$mine]]) : $val;
                $p[$val[$mine]] = &$result[$i];
            } else {
                $i = isset($p[$val[$parent]][$childField]) ? count($p[$val[$parent]][$childField]) : 0;
                $p[$val[$parent]][$childField][$i] = $val;
                $p[$val[$mine]] = &$p[$val[$parent]][$childField][$i];
            }
        }

        return $result;*/

        // 方案2
        $items = [];
        foreach ($data as $datum) {
            $items[$datum[$mine]] = $datum;
        }
        $tree = [];
        foreach ($items as $key => $item) {
            if (isset($items[$item[$parent]])) {
                $items[$item[$parent]][$childField][] =& $items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }

        return $tree;
    }

    //非常给力的authcode加密函数,Discuz!经典代码
    //函数authcode($string, $operation, $key, $expiry)中的$string：字符串，明文或密文；$operation：DECODE表示解密，其它表示加密；$key：密匙；$expiry：密文有效期。  
    function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {
        // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙     
        $ckey_length = 4;
        // 密匙     
        $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
        // 密匙a会参与加解密     
        $keya = md5(substr($key, 0, 16));
        // 密匙b会用来做数据完整性验证     
        $keyb = md5(substr($key, 16, 16));
        // 密匙c用于变化生成的密文     
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
        // 参与运算的密匙     
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，   
        //解密时会通过这个密匙验证数据完整性     
        // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确     
        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        // 产生密匙簿     
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度     
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        // 核心加解密部分     
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            // 从密匙簿得出密匙进行异或，再转成字符     
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ($operation == 'DECODE') {
            // 验证数据有效性，请看未加密明文的格式     
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
            // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码     
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }

    /**
     *  openssl_encrypt 加密
     */
    public static function encrypt($str, $key)
    {
        $method = 'AES-256-ECB';
        return openssl_encrypt($str, $method, $key);
    }

    /**
     *  openssl_decrypt 解密
     */
    public static function decrypt($str, $key)
    {
        $method = 'AES-256-ECB';
        return openssl_decrypt($str, $method, $key);
    }

    /**
     * 获取Public路径
     * @return string
     */
    public static function getPublicPath()
    {
        return EASYSWOOLE_ROOT . DIRECTORY_SEPARATOR . 'Public';
    }

    /**
     * 获取当日过去的秒数
     * @param int $time 时间戳
     * @return int
     */
    public static function getPastSeconds($time = 0)
    {
        $time || $time = time();
        return ($time + 8 * 3600) % 86400;
    }

    /**
     * 获取当日剩余秒数
     * @param int $time 时间戳
     * @return int
     */
    public static function getRemainingSeconds($time = 0)
    {
        $time || $time = time();
        return 86400 - ($time + 8 * 3600) % 86400;
    }

    /**
     * 分割ipLong的左右各16位
     * @param int $long IpLong值
     * @return int[]
     */
    public static function splitIpLong($long)
    {
        $left = $long >> 16;
        $right = $long & 65535;
        return [$left, $right];
    }

    /**
     * 转移临时上传文件
     * @param string $tempFile 文件目录
     * @param string $type
     * @return string
     * @throws Exception
     */
    public static function moveTempFile($tempFile, $type)
    {
        $tempFile = Func::getPublicPath() . $tempFile;
        $fileName = substr($tempFile, strrpos($tempFile, '/') + 1);

        $filePath = Upload::getImageDatePath($type) . DIRECTORY_SEPARATOR . $fileName;
        $targetPath = Func::getPublicPath() . DIRECTORY_SEPARATOR . $filePath;

        if (!is_file($tempFile)) {
            throw new Exception('未找到上传文件', Status::CODE_BAD_REQUEST);
        }

        $result = File::moveFile($tempFile, $targetPath);
        if (!$result) {
            throw new Exception('转移文件失败，请检查权限。', Status::CODE_BAD_REQUEST);
        }

        return DIRECTORY_SEPARATOR . $filePath;
    }

    /**
     * 计算缓存过期时间
     * @param $pageId
     * @return float|int
     */
    public static function getExpireTime($pageId)
    {
        $expire = mt_rand(600, 1800);
        return Func::getRemainingSeconds() + ($expire);
    }

    /**
     * 获取文件的mimetype
     * @param string $fullFileName 文件全路径
     * @return false|string
     */
    public static function getMimetype($fullFileName)
    {
        $finfo = finfo_open(FILEINFO_MIME);
        $mimetype = finfo_file($finfo, $fullFileName);
        finfo_close($finfo);
        if (strpos($mimetype, ';') !== false) {
            $mimetype = current(explode(';', $mimetype));
        }

        return $mimetype;
    }

    /**
     * 不区分大小写检查是否在数组中
     * @param string $path
     * @param array $list
     * @return bool
     */
    public static function inArrayNoCase($path, $list)
    {
        foreach ($list as $item) {
            if (strcasecmp($path, $item) === 0) {
                return true;
            }
        }

        return false;
    }

    public static function curlPost($url,$postData){
        $header = [
            'macct: mitao'
        ];
        $curl = curl_init(); // 启动一个CURL会话
        // 设置 cURL 选项
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, 1);             // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);   // Post提交的数据包x
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);         // 设置超时限制 防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0);           // 显示返回的Header区域内容
        curl_setopt ($curl, CURLOPT_HTTPHEADER, $header); // 设置HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        if(curl_errno($curl))
        {
            echo 'Errno'.curl_error($curl);//捕捉异常
        }
        curl_close($curl); // 关闭CURL会话

        return $tmpInfo;
    }

    public static function curlJsonPost($url,$postData){
        $header = [
            'Content-Type: application/json',
            'macct: mitao',
        ];
        $postData = json_encode($postData);
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)'); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, 1);             // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);   // Post提交的数据包x
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);         // 设置超时限制 防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0);           // 显示返回的Header区域内容
        curl_setopt ($curl, CURLOPT_HTTPHEADER, $header); // 设置HTTP头
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);   // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        if(curl_errno($curl))
        {
            echo 'Errno'.curl_error($curl);//捕捉异常
        }
        curl_close($curl); // 关闭CURL会话

        return $tmpInfo;
    }

    public static function encryptAES($data)
    {
        $json = json_encode($data);
        $key = Config::getInstance()->getConf('API_ASE.key');
        if(!$key){
            $key = "2dccd1ab3e03990aea77359831c85ca2";
        }
        $cipher = "AES-128-ECB";
        $options = OPENSSL_RAW_DATA;
        $iv = "";

        $encryptedData = openssl_encrypt($json, $cipher, $key, $options, $iv);

        // 对加密结果进行 Base64 编码
        $base64EncryptedData = base64_encode($encryptedData);

        return $base64EncryptedData;
    }

    public static function decryptAES($encryptedData)
    {
        $key = Config::getInstance()->getConf('API_ASE.key');
        if(!$key){
            $key = "2dccd1ab3e03990aea77359831c85ca2";
        }
        $cipher = "AES-128-ECB";
        $options = OPENSSL_RAW_DATA;
        $iv = "";

        // 对 Base64 编码的加密数据进行解码
        $encryptedData = base64_decode($encryptedData);

        $decryptedData = openssl_decrypt($encryptedData, $cipher, $key, $options, $iv);

        return $decryptedData;
    }

    public static function iSIpV4($ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
    }

    public static function isIpV6($ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
    }

    public static function ipv6_to_ipv4($ipv6) {
        // 去掉 IPv6 地址中的冒号
        $ipv6 = str_replace(':', '', $ipv6);

        // 将 IPv6 地址转换为十进制数
        $decimal = hexdec(substr($ipv6, -8));

        // 将十进制数转换为 IPv4 地址
        return long2ip($decimal);
    }
}
