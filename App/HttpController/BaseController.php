<?php

namespace App\HttpController;

use App\Utility\Func;
use App\Utility\LogHandler;
use App\Utility\Response\CommonResponse;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\ServerManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Jwt\Jwt;

class BaseController extends AnnotationController
{
    public function index()
    {
        $this->actionNotFound('index');
    }

    /**
     * 获取用户的真实IP
     * 这个是根据在 nginx 中的配置来的，详见 nginx 的网站配置或转发配置
     * @param string $headerName 代理服务器传递的标头名称
     * @return string
     */
    protected function clientRealIP(string $headerName = 'x-real-ip'): string
    {
        $server = ServerManager::getInstance()->getSwooleServer();
        $client = $server->getClientInfo($this->request()->getSwooleRequest()->fd);
        // $clientAddress = $client['remote_ip'];
        $xri = $this->request()->getHeader($headerName);
        $xff = $this->request()->getHeader('x-forwarded-for');

        // var_dump($clientAddress,$xri,$xff);
        // 因为如果用api.xxx.xx转发请求到9501后，remote_ip获取到的总是本机ip，然而xri和xff可以拿到正确的客户端ip
        // 现在都先检查xri和xff
        // 注意！当有CDN代理时应该先判断xff后判断xri
        $clientAddress = '';
        if (!empty($xff)) {
            $list = explode(',', $xff[0]);
            if (isset($list[0])) $clientAddress = $list[0];
        } elseif (!empty($xri)) {
            $clientAddress = $xri[0];
        }

        $ip = $clientAddress ?: $client['remote_ip'];
        return Func::iSIpV4($ip) ? $ip : Func::ipv6_to_ipv4($ip);
    }

    /**
     * 获取请求参数
     * @param string $name
     * @param null $default
     * @return array|mixed|object|null
     */
    protected function input(string $name, $default = null)
    {
        $value = $this->request()->getRequestParam($name);
        return $value ?? $default;
    }

    /**
     * 重写Json返回格式，加上系统时间
     * @param int $statusCode
     * @param mixed $result
     * @param string $msg
     * @return bool
     */
    protected function writeJson($statusCode = 200, $result = [], $msg = '')
    {
        if (!$this->response()->isEndResponse()) {
            $timestamp = time();

            $data = [
                'code' => $statusCode,
                'hash' => false,
                'result' => $result,
                'systemTimestamp' => $timestamp,
                'systemDateTime' => date('Y-m-d H:i:s', $timestamp),
                'msg' => $statusCode != Status::CODE_INTERNAL_SERVER_ERROR ? $msg : (Core::getInstance()->runMode() == 'dev' ? $msg : '请稍后重试'),
            ];
            $hash = Config::getInstance()->getConf('API_ASE.hash');
            if($hash){
                if(!empty($data['result'])){
                    $data['hash'] = true;
                    $data['result'] = Func::encryptAES($data['result']);
                }
            }

            // 如果 $msg 中包含SQL则强制覆盖
            // 因为mysql扩展的错误是不会有500的状态码的，要单独判断。
            if ((strrpos(strtoupper($msg), 'SQLSTATE') !== false)) {
                LogHandler::getInstance()->log($msg, LogHandler::LOG_LEVEL_ERROR, 'SQLSTATE error');
                if (Core::getInstance()->runMode() != 'dev') {
                    $data['msg'] = '请稍后重试';
                }
            }

            // 2023-07-06 修改，为了避免被浏览器抓文字，所有内容均以unicode形式编码
            //if (Core::getInstance()->runMode() == 'produce') {
            //    $this->response()->write(json_encode($data, JSON_UNESCAPED_SLASHES));
            //} else {
            //    $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            //}

            // 2023-08-17 因为对接安卓需要保持{}和[]的一致，所以将部分接口的数据做一下替换
            $path = $this->request()->getUri()->getPath();
            $specialInterface = Config::getInstance()->getConf('SPECIAL_INTERFACE');

            if (isset($specialInterface[$path])) {
                /**
                 * @var $responseClass CommonResponse
                 */
                $responseClass = $specialInterface[$path];
                $response = $responseClass::getInstance()->exec($data);
            } else {
                $response = CommonResponse::getInstance()->exec($data);
            }

            $this->response()->write($response);

            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            // 因400状态码无法收到响应数据，则将所有headers中的400状态改为200，然后保持response.code为400
            // 因生产环境web服务器除200状态外并不会返回响应数据，即将所有的返回状态码设置为200，response.code保持不变用作判断
            $this->response()->withStatus($statusCode != Status::CODE_INTERNAL_SERVER_ERROR ? Status::CODE_OK : Status::CODE_INTERNAL_SERVER_ERROR);
            // 如果是500的情况则记录日志
            if ($statusCode == Status::CODE_INTERNAL_SERVER_ERROR) {
                LogHandler::getInstance()->log($msg, LogHandler::LOG_LEVEL_ERROR, 'writeJson 500 error');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $payload 自定义数据
     * @param bool $isAdmin 是否是后台token
     * @return string
     */
    public function generateToken($payload, $isAdmin = false): string
    {
        if ($isAdmin) {
            $jwtConfig = Config::getInstance()->getConf('ADMIN_JWT');
        } else {
            $jwtConfig = Config::getInstance()->getConf('JWT');
        }
        $jwtObject = Jwt::getInstance()
            ->setSecretKey($jwtConfig['secretKey']) // 秘钥
            ->publish();

        $jwtObject->setAud('user'); // 用户
        $jwtObject->setExp(time() + $jwtConfig['expire']); // 过期时间
        $jwtObject->setIat(time()); // 发布时间
        $jwtObject->setIss($jwtConfig['issuer']); // 发行人

        // 自定义数据
        $jwtObject->setData($payload);

        // 最终生成的token
        return $jwtObject->__toString();
    }
}
