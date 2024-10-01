<?php

namespace App\HttpController\Api\User;

use App\Service\User\UserService;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use Exception;
use Throwable;

/**
 * Class Login
 * @package App\HttpController\Api\User
 * @ApiGroup(groupName="用户-登录 User/Login")
 * @ApiGroupDescription("用户登录相关的操作")
 */
class Login extends UserBase
{

    /**
     * 通过设备登陆
     * @Api(name="通过设备登陆",path="/Api/User/Login/deviceLogin")
     * @ApiDescription("通过设备登陆")
     * @Method(allow=["POST"])
     * @Param(name="deviceId", alias="设备id", type="string", required="", mbLengthMin="1", description="设备id")
     * @Param(name="pageName", alias="页面名字", type="string", required="", mbLengthMin="1", description="页面名字")
     * @Param(name="inviteCode", alias="邀请码", type="string", optional="", mbLengthMin="1", description="邀请码")
     * @apiSuccess({"code":200,"result":{"token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE2OTc1NDgxMjQsInN1YiI6bnVsbCwibmJmIjoxNjk3NDYxNzI0LCJhdWQiOiJ1c2VyIiwiaWF0IjoxNjk3NDYxNzI0LCJqdGkiOiIxYWpjT0JTTlBrIiwiaXNzIjoiZXNkaCIsInN0YXR1cyI6MSwiZGF0YSI6eyJ1c2VySWQiOjEwMDAwMSwidXNlclR5cGUiOiJNZW1iZXIiLCJuaWNrbmFtZSI6Iua4uOWuojY1MkQxRTgyRThCOEIifX0.d4xiGUJ3dZCB2EzxvjnfIjnOFEG4w5eN-7ophxNJ6g4","userInfo":{"userId":100001,"userGroupId":1,"nickname":"游客652D1E82E8B8B","balance":"0.00"}},"systemTimestamp":1697461724,"systemDateTime":"2023-10-16 21:08:44","msg":"OK"})
     */
    public function deviceLogin()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();
            $data = [
                'deviceId' => trim($param['deviceId']),
                'pageName' => trim($param['pageName']),
                'inviteCode' => trim($param['inviteCode'] ?? ''),
                'ip' => $ip,
            ];
            $user = UserService::getInstance()->deviceLogin($data);

            /**
             * 这里的值对应了$this->who里面的值
             * 比如要取token的userId就是$this->who['userId']
             */
            $payload = $this->getUserTokenData($user);
            $token = $this->generateToken($payload);

            $loginData = $this->getUserLoginData($user);
            $result = [
                'token' => $token,
                'userInfo' => $loginData,
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 通过身份卡登陆
     * @Api(name="通过身份卡登陆",path="/Api/User/Login/identityCardLogin")
     * @ApiDescription("通过身份卡登陆")
     * @Method(allow=["POST"])
     * @Param(name="identityCard", alias="身份卡", type="string", required="", mbLengthMin="1", description="身份卡")
     * @apiSuccess({"code":200,"result":{"token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3MDEzNDk1ODgsInN1YiI6bnVsbCwibmJmIjoxNzAxMjYzMTg4LCJhdWQiOiJ1c2VyIiwiaWF0IjoxNzAxMjYzMTg4LCJqdGkiOiJWNTJSWWNqVG5CIiwiaXNzIjoiZXNkaCIsInN0YXR1cyI6MSwiZGF0YSI6eyJ1c2VySWQiOjEwMDAwMSwidXNlclR5cGUiOiJNZW1iZXIiLCJuaWNrbmFtZSI6Iua4uOWuojY1MzhDREE2NTAyMjEiLCJkZXZpY2VJZCI6ImFhZmYxN2U3MzNhZTIxZGY4MDg5NDA5YmI3MjNkZmM2In19.zYfIbEtWjI8b7MeofaGVdPFveIUTJBcFqN94xpm8HTs","userInfo":{"userId":100001,"userGroupId":1,"nickname":"游客6538CDA650221","balance":"0.00"}},"systemTimestamp":1701263188,"systemDateTime":"2023-11-29 21:06:28","msg":"OK"})
     */
    public function identityCardLogin()
    {

        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();
            $data = [
                'identityCard' => trim($param['identityCard']),
                'ip' => $ip,
            ];
            $user = UserService::getInstance()->identityCardLogin($data);

            /**
             * 这里的值对应了$this->who里面的值
             * 比如要取token的userId就是$this->who['userId']
             */
            $payload = $this->getUserTokenData($user);
            $token = $this->generateToken($payload);

            $loginData = $this->getUserLoginData($user);
            $result = [
                'token' => $token,
                'userInfo' => $loginData,
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 通过短信登陆
     * @Api(name="通过短信登陆",path="/Api/User/Login/messageLogin")
     * @ApiDescription("通过短信登陆")
     * @Method(allow=["POST"])
     * @Param(name="phoneNumber", alias="手机号码", type="string", required="", mbLength="11", description="手机号码")
     * @Param(name="messageId", alias="消息id", type="string", required="", mbLengthMin="1", description="消息id")
     * @Param(name="code", alias="验证码", type="string", required="", mbLengthMin="1", description="验证码")
     * @apiSuccess({"code":200,"result":{"token":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJleHAiOjE3MDEzNDk1ODgsInN1YiI6bnVsbCwibmJmIjoxNzAxMjYzMTg4LCJhdWQiOiJ1c2VyIiwiaWF0IjoxNzAxMjYzMTg4LCJqdGkiOiJWNTJSWWNqVG5CIiwiaXNzIjoiZXNkaCIsInN0YXR1cyI6MSwiZGF0YSI6eyJ1c2VySWQiOjEwMDAwMSwidXNlclR5cGUiOiJNZW1iZXIiLCJuaWNrbmFtZSI6Iua4uOWuojY1MzhDREE2NTAyMjEiLCJkZXZpY2VJZCI6ImFhZmYxN2U3MzNhZTIxZGY4MDg5NDA5YmI3MjNkZmM2In19.zYfIbEtWjI8b7MeofaGVdPFveIUTJBcFqN94xpm8HTs","userInfo":{"userId":100001,"userGroupId":1,"nickname":"游客6538CDA650221","balance":"0.00"}},"systemTimestamp":1701263188,"systemDateTime":"2023-11-29 21:06:28","msg":"OK"})
     */
    public function messageLogin()
    {

        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();
            $data = [
                'phoneNumber' => trim($param['phoneNumber']),
                'messageId' => trim($param['messageId']),
                'code' => trim($param['code']),
                'ip' => $ip,
            ];
            $user = UserService::getInstance()->messageLogin($data);

            /**
             * 这里的值对应了$this->who里面的值
             * 比如要取token的userId就是$this->who['userId']
             */
            $payload = $this->getUserTokenData($user);
            $token = $this->generateToken($payload);

            $loginData = $this->getUserLoginData($user);
            $result = [
                'token' => $token,
                'userInfo' => $loginData,
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
}