<?php

namespace App\RedisKey\VerifyCode;

class VerifyCodeKey
{
    /*
     * 图形验证码
     */
    public static function imageCodeHash(string $codeType, string $verifyUniqueId)
    {
        return 'verifyCodeHash_' . $codeType . '_' . $verifyUniqueId;
    }

    /*
     * 图形验证码
     */
    public static function imageCodeTime(string $codeType, string $verifyUniqueId)
    {
        return 'verifyCodeTime_' . $codeType . '_' . $verifyUniqueId;
    }

    /*
     * 短信验证码
     */
    public static function smsCode(string $phoneNumber, string $messageId)
    {
        return 'smsCode_' . $phoneNumber . '_' . $messageId;
    }

    /*
     * 短信验证码频率限制
     */
    public static function smsCodeTimeLimit(string $phoneNumber)
    {
        return 'smsCodeTimeLimit_' . $phoneNumber;
    }

    /**
     * 短信验证码单IP次数限制
     */
    public static function smsCodeIpLimit(string $ipLong)
    {
        return 'smsCodeIpLimit_' . $ipLong;
    }
}