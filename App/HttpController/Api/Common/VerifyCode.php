<?php

namespace App\HttpController\Api\Common;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\RedisKey\VerifyCode\VerifyCodeKey;
use App\Service\Message\JSMSService;
use App\Utility\Func;
use App\Utility\VerifyCodeTools;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\Random;

/**
 * Class VerifyCode
 * @package App\HttpController\Api\Common
 * @ApiGroup(groupName="公共 验证码 Common/VerifyCode")
 * @ApiGroupDescription("验证码相关的操作")
 */
class VerifyCode extends CommonBase
{
    /**
     * 获取图形验证码
     * @Api(name="获取图形验证码",path="/Api/Common/VerifyCode/getImageCode")
     * @ApiDescription("获取图形验证码")
     * @Method(allow=["GET", "POST"])
     * @Param(name="codeType", alias="验证码类型", required="", inArray=["register", "login", "forgotPwd", "adminLogin"], description="验证码类型(register(注册),(login(登录)),forgotPwd(忘记密码),adminLogin(管理员登录))")
     * @apiSuccess({"code":200,"result":{"verifyCode":"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKIAAAAyCAMAAADoSvBqAAAAilBMVEX///9RSkDg4aOkqNy6sLmb2sChqZrL2J3Pnpuvo8rfn7evparT0c/p6OeQdG1mYFeSjYeAaWJwX1aopJ98d2+9u7d6eY5lYWdbVVOffnhwbXqEhKFbVUt0a3NvbWGXnY55eW1lYVZcVU10bGdgW0u7xpFvbVestIV+f2JiVE6pf4qGaWy7iZmRyLD2hdGiAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAEzklEQVRYhc2ZiZrbJhCAwbYs2yuCWqFs2qZNj922ud7/9TIHSNxYtpuv89laViD4NTMwAxbCk6cn/iZyPq9XlP3eqzweM0+45ufzIV97mzBdjrGGWCKE5iwPJKxoEenscETnIRYJhTiAPJgQ+fKEViVIugGRn3sgnqhpkdUoWI37wBXrEiB2XQffewmrlr5TJ0jX/a8RGW5BbHiILycQj7Fp6O+PiHweY3m6PBrxejktl1qjk6frmyXwxeMGLWY6yvj0/YAimtEOURnVeOwkIv10/LL/BSJ3bwsWUWn58tpAPEXj510Gmwyt170BcdBSyr8biA6ggfgG+hrvxJtHoyf3DyEORqIMD0H8AXr6sdiLGkC4BBJVDtNoDBZG6GP2EWcpgREM3fd9qev+WkOLN/i2RcQJKnVQ8PnxHqKhUZWlwws2lka/Il+JEe/Hy0kBcZC2/+2IpD3Dfkd1FnEkKxtLV2AsqzcRVfGZ87mOaNU4G63ZnY8oI1nZMIWaX+9GJCtN6W30ohaiGIHNwNXrQaEKGRl6AKeU/yzNXaCgnGoDInaZTml2o37WVkFLIRDwEi1njZegP0nLNjyO5RfnRoQHF8z5NuR95FAm0SKZ4cxapEm7FOL3Qz74rN4MazYYWsON/l+NJnqxYWaZGikiphClLAK7VGKY45t9j/OqjTggIBrW71AbbjxiBX1UghgIZ2IVRJ2Z0g5xdGRjHlEYmCrgAoEP0AyUPK+10fzk5VJErOWzLNJfeHc7+OxEh4w9aBHHHwalpqwv2kAndegpAymWECda1GA2XS7OF5MdSBsxmNLIh5x2RqPyCOJ9QYuCtKSjVWtG8+LSMzGvkSMguhmd0SIMVkM0/uhIR9fV0KynEiKpUceOQlNZGjLOrD9C+dNlqcz4Yt+TL8b7eifBkuch4vb1Vx/xt+wb4qRIXWDyIsJnLH+uICJfX0Oc/SgdaJFnSQNxximbI0frg3bVR7S4/FJBFM7QJcQgBAaIXQfhQ0/qwwcFhfe/p4E/fkOfEfAmrWjB0HK6XDJtWojzZNjNYWWYnDMtiJildzb0QODlQpI+iWoeglGBl55BNBFXuBVRLjNZDXOyeLM4RPEHFk65PVs5D6F8ggIMEsaIuwwi0XknWtM0YGB5a56hk+cGolkRkzbgDNk35DADtV+Ar43ogi5x8t4Ukma9TIV3+bBOvoiIVDglezaUQqokXJhp7gSZMcjOrL1+cnj4/TmPuKRBVosZXyykSiS0bH/tus0nPnaQYUV8ntxCcDisVzv8tBROyYYIpRS+aYyR9bv5rMIeQlBYGd/+ooDvDkRYpf/8qz7gjcdmMBJu7vbMV0Bc/IwLWUNDNleLrzcRBsqoIxIA/YHNqsou3W0pEdYyb/+0KWqXIF4jtXS0psMao4dAGtyHVYcIscldROytFDArjAFitJNAvpiGuYvdtXwxtyGlpbyIeCBZEIXf9rAgJY9UGLYjcrC5Voth24A/+8RmxFsIa4ilB27XYt7MABkOerSSQ7xi413xRaKrIWZPRliLmQQtjygaPwnRLhw+ecYWYuHsBunSBC1CvHrte3KMperaslg8XWLGIqHzrd0ubnIDYlXq518JYvBrg9suXyHfDzH6PWS3u5LwLsSUaXXAb2PeOwSIqmGhAAAAAElFTkSuQmCC","verifyCodeTime":1685080897,"verifyUniqueId":"ececd77e823ee41b4ab7ad0d3f935b9d"},"systemTimestamp":1685080897,"systemDateTime":"2023-05-26 14:01:37","msg":"success"})
     */
    public function getImageCode()
    {
        $param = $this->request()->getRequestParam();

        try {
            $codeType = $param['codeType'] ?? '';
            $config = new \EasySwoole\VerifyCode\Conf([
                'useCurve' => false, # 设置不开启 混淆曲线，默认不开启
                'useNoise' => true, # 设置不开启 随机噪点，默认不开启
                'length' => SystemConfigKey::VERIFY_CODE_LENGTH,     # 设置 验证码位数，默认 4 位
            ]);
            $code = new \EasySwoole\VerifyCode\VerifyCode($config);
            // 获取随机数(即验证码的具体值)
            $random = Random::character(SystemConfigKey::VERIFY_CODE_LENGTH, '123456789abcdefghijklmnpqrstuvwxyz');
            // 绘制验证码
            $drawCode = $code->DrawCode($random);
            // 获取验证码的 base64 编码及设置验证码有效时间
            $time = time();
            $uniqueId = Func::CreateGuid();
            $result = [
                'verifyCode' => $drawCode->getImageBase64(), // 得到绘制验证码的 base64 编码字符串
                'verifyCodeTime' => $time,
                'verifyUniqueId' => $uniqueId
            ];
            //将验证码加密存储在Redis中，方便后续验证。
            $redis = RedisPool::defer(RedisDb::REDIS_DB_SESSION);
            $redis->set(VerifyCodeKey::imageCodeHash($codeType, $uniqueId), VerifyCodeTools::getVerifyCodeHash($random, $time), SystemConfigKey::VERIFY_CODE_TTL);
            $redis->set(VerifyCodeKey::imageCodeTime($codeType, $uniqueId), $time, SystemConfigKey::VERIFY_CODE_TTL);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }

    /**
     * 获取短信验证码
     * @Api(name="获取短信验证码",path="/Api/Common/VerifyCode/getMessageCode")
     * @ApiDescription("获取短信验证码")
     * @Param(name="phoneNumber", alias="手机号码", type="string", required="", mbLength="11", description="手机号码")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess({"code":200,"result":{"messageId":"65643b97416d3"},"systemTimestamp":1701067671,"systemDateTime":"2023-11-27 14:47:51","msg":"success"})
     */
    public function getMessageCode()
    {
        $param = $this->request()->getRequestParam();

        try {
            // 这里直接写在这里了，并没有用统一的常量管控，如果后续有需要再说。现在没这个时间和精力。
            $code = mt_rand(1000, 9999);
            $phoneNumber = trim($param['phoneNumber']);
            $ip = $this->clientRealIP();

            $result = [];
            $result['messageId'] = JSMSService::getInstance()->sendCode($phoneNumber, $code, $ip);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }
}
