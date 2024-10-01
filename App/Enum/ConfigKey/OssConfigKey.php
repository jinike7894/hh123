<?php

namespace App\Enum\ConfigKey;

class OssConfigKey
{
    const AWS_S3_ENABLED = 'AwsS3Enabled'; // 是否启用
    const AWS_S3_ACCESS_ID = 'AwsS3AccessId'; // id
    const AWS_S3_ACCESS_KEY = 'AwsS3AccessKey'; // key
    const AWS_S3_ENDPOINT = 'AwsS3Endpoint'; // 端点
    const AWS_S3_REGION = 'AwsS3Region'; // 地区
    const AWS_S3_BUCKET = 'AwsS3Bucket'; // 桶名
    const AWS_S3_HOST = 'AwsS3Host'; // 域名

    const ALL_KEY = [
        self::AWS_S3_ENABLED,
        self::AWS_S3_ACCESS_ID,
        self::AWS_S3_ACCESS_KEY,
        self::AWS_S3_ENDPOINT,
        self::AWS_S3_REGION,
        self::AWS_S3_BUCKET,
        self::AWS_S3_HOST,
    ];
}