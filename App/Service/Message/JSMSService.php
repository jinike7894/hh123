<?php

namespace App\Service\Message;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\Model\Common\ConfigModel;
use App\Model\Common\SendCodeModel;
use App\RedisKey\VerifyCode\VerifyCodeKey;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Http\Message\Status;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use JiGuang\JSMS;

class JSMSService
{
    use Singleton;

    /**
     * 发送验证码
     * @param $phoneNumber
     * @param $code
     * @param string $ip
     * @return mixed|string
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function sendCode($phoneNumber, $code, $ip = '')
    {
        $timeLimitKey = VerifyCodeKey::smsCodeTimeLimit($phoneNumber);

        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $exists = $redis->get($timeLimitKey);
        if ($exists) {
            throw new Exception('操作过于频繁，请稍后再试。', Status::CODE_BAD_REQUEST);
        }

        $ipLimitKey = VerifyCodeKey::smsCodeIpLimit(ip2long($ip));
        $ipTimes = $redis->get($ipLimitKey);
        $config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_JSMS);
        if ($ipTimes >= $config['JSIpLimit']) {
            throw new Exception('当前ip发送次数过多。', Status::CODE_BAD_REQUEST);
        }

        $content = ['code' => $code];
        $sendCodeData = [
            'type' => SendCodeModel::TYPE_MS,
            'channel' => SendCodeModel::CHANNEL_JSMS,
            'requestIp' => $ip,
            'requestIpLong' => ip2long($ip),
            'target' => $phoneNumber,
            'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
        ];

        // 开发模式不进行短信发送，模拟生成一条数据即可。
        if (Core::getInstance()->runMode() != 'dev') {

            if ($phoneNumber[0] != '1') {
                throw new Exception('手机号码不正确，请确认后再试。', Status::CODE_BAD_REQUEST);
            }

            $config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_JSMS);
            $JSMSClient = new JSMS($config['JSAppKey'], $config['JSMasterSecret']);
            $response = $JSMSClient->sendMessage($phoneNumber, $config['JSTemplateId'], $content);

            $httpCode = $response['http_code'] ?? 0;
            $sendCodeData['response'] = json_encode($response, JSON_UNESCAPED_UNICODE);

            if ($httpCode != 200) {
                $sendCodeData['status'] = SendCodeModel::STATUS_FAIL;
                SendCodeModel::create($sendCodeData)->save();
                throw new Exception('短信发送失败，请稍后再试。', Status::CODE_BAD_REQUEST);
            }

            $messageId = $response['body']['msg_id'] ?? '';
            if (!$messageId) {
                $sendCodeData['status'] = SendCodeModel::STATUS_FAIL;
                SendCodeModel::create($sendCodeData)->save();
                throw new Exception('未获取到短信ID，请稍后再试。', Status::CODE_BAD_REQUEST);
            }

        } else {
            $messageId = uniqid();
        }

        $sendCodeData['status'] = SendCodeModel::STATUS_SUCCESS;
        SendCodeModel::create($sendCodeData)->save();

        $redis->setEx($timeLimitKey, SystemConfigKey::VERIFY_SMS_CODE_TIME_LIMIT, $messageId);

        $redis->incr($ipLimitKey);
        $redis->expire($ipLimitKey, Func::getRemainingSeconds());

        $smsKey = VerifyCodeKey::smsCode($phoneNumber, $messageId);
        $redis->setEx($smsKey, SystemConfigKey::VERIFY_SMS_CODE_TTL, $code);

        return $messageId;
    }

    /**
     * 检查验证码
     * @param $phoneNumber
     * @param $messageId
     * @param $code
     * @return bool
     */
    public function checkCode($phoneNumber, $messageId, $code)
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
        $smsKey = VerifyCodeKey::smsCode($phoneNumber, $messageId);
        $cacheCode = $redis->get($smsKey);

        $result = $cacheCode && $cacheCode == $code;

        // 如果是验证成功的，要删除这个码，一码一次。
        if ($result) {
            $redis->del($smsKey);
        }

        return $result;
    }
}