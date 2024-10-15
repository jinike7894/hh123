<?php

namespace App\HttpController\Api\Admin;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\HttpController\Api\ApiBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Admin\AdminModel;
use App\Model\Admin\AuthModel;
use App\Model\Common\ConfigModel;
use App\RedisKey\SystemRedisKey;
use App\Service\Navigation\AdClickStatisticService;
use App\Utility\Func;
use App\Utility\LogHandler;
use EasySwoole\Component\Timer;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\Jwt\Jwt;

// 因为官方的PolicyNode的search方法存在BUG，则现在使用重写的PolicyNode类
// use EasySwoole\Policy\Policy
// use EasySwoole\Policy\PolicyNode
use App\Utility\Policy\Policy;
use App\Utility\Policy\PolicyNode;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

class AdminBase extends ApiBase
{
    // public 才会根据协程清除
    public $jwt;
    /**
     * @var array
     */
    public $who = [];

    // 白名单
    protected $basicAction = [
        '/Api/Admin/Login/login',
        '/Api/Admin/Account/checkGoogleAuthenticator', // 这个等做的时候一起改
        '/Api/Admin/Navigation/AdClickStatistic/getTotalList',
        '/Api/Admin/System/FastUpdate/setConfig',
    ];

    public function index()
    {
        $this->actionNotFound('index');
    }

    /**
     * onRequest
     * @param null|string $action
     * @return bool|null
     * @throws Throwable
     */
    function onRequest(?string $action): ?bool
    {
        if (!parent::onRequest($action)) {
            return false;
        }
        // 判断是否开启维护
        $maintain = ConfigModel::create()->getConfigValue(SystemConfigKey::WEBSITE_MAINTENANCE);
        $maintain = json_decode($maintain, true);
        if ($maintain['status'] == 1) {
            throw new Exception($maintain['content'], Status::CODE_BAD_REQUEST);
        }

        $path = $this->request()->getUri()->getPath();

        // 不需要登录的则跳过
        if (!Func::inArrayNoCase($path, $this->basicAction)) {
            // 获取JWT信息
            $status = $this->checkJwt();
            if ($status < 0) {
                switch ($status) {
                    case  -1:
                        // echo '无效';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入Token无效');
                        return false;
                    case  -2:
                        // echo 'token过期';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入已过期');
                        return false;
                    case  -3:
                        // echo 'token解析异常';
                        $this->writeJson(Status::CODE_UNAUTHORIZED, '', '登入检查失败');
                        return false;
                }
            }

            // 权限策略判断
            if (!$this->vifPolicy($this->who['adminId'], $path)) {
                $this->writeJson(Status::CODE_BAD_REQUEST, '', '无权限访问该接口');
                return false;
            }
        }

        return true;
    }

    function checkJwt(): int
    {
        $token = $this->request()->getHeader('authorization');
        if (empty($token)) {
            return -1;
        }

        try {
            $token = current($token);
            $jwtConfig = Config::getInstance()->getConf('ADMIN_JWT');
            $jwtObject = Jwt::getInstance()->setSecretKey($jwtConfig['secretKey'])->decode($token);

            $status = $jwtObject->getStatus();
            if ($status == 1) {

                if ($jwtConfig['issuer'] != $jwtObject->getIss()) {
                    return -1;
                }

                $this->jwt = $jwtObject;
                $this->who = $jwtObject->getData();
            }

            // 这里有个特别特殊的处理
            // 如果开启了只允许单设备登录则要将jwt和对应的用户id存入redis中当做session来使用
            // 所以这里要进行token的对比验证
            if ($status != -2) {
                $forceSingleDeviceLogin = ConfigModel::create()->getConfigValue(SystemConfigKey::FORCE_SINGLE_DEVICE_LOGIN);
                if ($forceSingleDeviceLogin) {
                    $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
                    $key = SystemRedisKey::session($this->who['adminId'], $this->who['adminType']);
                    $cacheToken = $redis->get($key);
                    if ($token != $cacheToken) {
                        return -2;
                    }
                }
            }

        } catch (\EasySwoole\Jwt\Exception $e) {
            return -3;
        }

        return $status;
    }

    /**
     * 验证权限策略
     * @param $u_id
     * @param string $path
     * @return bool
     * @throws
     */
    private function vifPolicy($u_id, string $path)
    {
        if (empty($u_id)) return false;
        // 该路径接口不需要验证 直接通过
        if ($this->shouldVifPath($path) == false) {
            return true;
        }

        $redis = RedisPool::defer(RedisDb::REDIS_DB_AUTH);
        // 从缓存拿 没有就从数据库读取
        $policy = $redis->get('policy_' . $u_id);
        if ($policy === null) {
            $policy = new Policy();
            // 用户权限
            $userModel = AdminModel::create()->get($u_id);
            $userAuth = $userModel->getAuth();

            foreach ($userAuth as $value) {
                $policy->addPath($value['authRule'], PolicyNode::EFFECT_ALLOW);
                // 有一些分组的 authRule 是空字符串，不用添加
                // $value['authRule'] && $policy->addPath($value['authRule'], PolicyNode::EFFECT_ALLOW);
            }
            $redis->set('policy_' . $u_id, serialize($policy), 86400);
        } else {
            $policy = unserialize($policy);
        }

        if ($policy->check($path) === 'allow') {
            return true;
        }
        return false;
    }

    /**
     * 该路径是否建立了权限管理  没建立就是不用管
     * @param string $path
     * @return bool
     * @throws
     */
    private function shouldVifPath(string $path): bool
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_AUTH);
        $authRes = $redis->get('shouldvif_api_' . md5($path));
        if ($authRes === null) {
            $auth = AuthModel::create()->get(['authRule' => $path]);
            // 没有设置该api规则 所以不需要验证
            if ($auth === null) {
                $redis->set('shouldvif_api_' . md5($path), false, 86400);
                return false;
            } else {
                $redis->set('shouldvif_api_' . md5($path), true, 86400);
                return true;
            }
        }
        if ($authRes === true) {
            return true;
        }
        return false;
    }

    /**
     * 重写Json返回格式，添加用户日志
     * @param int $statusCode
     * @param mixed $result
     * @param string $msg
     * @return bool
     */
    /**
     * 下载导出的excel文件
     * @param $headers
     * @param $list
     * @param $fileName
     * @throws Exception
     */
    protected function downloadExcel($headers, $list, $fileName)
    {
        $filePath = Func::excel($headers, $list, $fileName);
        $this->downloadExcelCommon($filePath, $fileName);
    }

    /**
     * 广告点击总计统计的excel
     * @param $headers
     * @param $list
     * @param $fileName
     */
    protected function exportAdClickStatisticTotalList($headers, $list, $fileName)
    {
        $filePath = AdClickStatisticService::getInstance()->exportTotalList($headers, $list, $fileName);
        $this->downloadExcelCommon($filePath, $fileName);
    }

    /**
     * 导出excel公共部分
     * @param $filePath
     * @param $fileName
     */
    private function downloadExcelCommon($filePath, $fileName)
    {
        $this->response()->sendFile($filePath);
        // 设置文件流内容类型，这里以 xlsx 为例
        $this->response()->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        // 设置要下载的文件名称，一定要带文件类型后缀
        $this->response()->withHeader('Content-Disposition', 'attachment;filename=' . $fileName);
        $this->response()->withHeader('Cache-Control', 'max-age=0');
        $this->response()->withStatus(200);
        $this->response()->end();

        // 5分钟后删除文件
        Timer::getInstance()->after(300000, function () use ($filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        });
    }
     //变更参数
     function convertKeysToCamelCase($array) {
        $newArray = [];
    
        foreach ($array as $key => $value) {
            // 将字段名中的下划线替换为空格
            $key = str_replace('_', ' ', $key);
            // 使每个单词的首字母大写
            $key = ucwords($key);
            // 去掉空格，形成驼峰命名法
            $key = lcfirst(str_replace(' ', '', $key));
            
            // 将新键和原值添加到新数组中
            $newArray[$key] = $value;
        }
    
        return $newArray;
    }
}
