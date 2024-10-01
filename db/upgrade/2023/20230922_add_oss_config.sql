# 1.增加亚马逊s3相关配置
# 2.广告文件类型新增s3类型

ALTER TABLE `config`
    MODIFY COLUMN `configGroup` enum('Test','System','WebSite','Navigation','App','Video','Oss') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n移动应用 App\r\n视频 Video\r\n存储 Oss' AFTER `configId`;

ALTER TABLE `nav_ad`
    MODIFY COLUMN `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3' AFTER `adName`;

# 这个是有用到s3的
INSERT INTO `config` VALUES (22, 'Oss', 'AwsS3Enabled', '1', '亚马逊S3启用', '1.是 0.否', NOW(), NOW());
INSERT INTO `config` VALUES (23, 'Oss', 'AwsS3AccessId', 'AKIAV4O6TTMDHK4K7JEX', '亚马逊S3AccessID', '亚马逊S3AccessID', NOW(), NOW());
INSERT INTO `config` VALUES (24, 'Oss', 'AwsS3AccessKey', 'NtsQjABhOIv8hxdnY4JKXdgmfZ1JcPzMuwtyRJOO', '亚马逊S3AccessKey', '亚马逊S3AccessKey', NOW(), NOW());
INSERT INTO `config` VALUES (25, 'Oss', 'AwsS3Endpoint', 'https://s3.ap-east-1.amazonaws.com', '亚马逊S3端点', '亚马逊S3端点', NOW(), NOW());
INSERT INTO `config` VALUES (26, 'Oss', 'AwsS3Region', 'ap-east-1', '亚马逊S3地区', '亚马逊S3地区', NOW(), NOW());
INSERT INTO `config` VALUES (27, 'Oss', 'AwsS3Bucket', 'mocha-video', '亚马逊S3桶名', '亚马逊S3桶名', NOW(), NOW());
INSERT INTO `config` VALUES (28, 'Oss', 'AwsS3Host', 'https://mocha-video.s3.ap-east-1.amazonaws.com', '亚马逊S3域名', '亚马逊S3域名', NOW(), NOW());

# 这个是不需要开启s3的
INSERT INTO `config` VALUES (22, 'Oss', 'AwsS3Enabled', '0', '亚马逊S3启用', '1.是 0.否', NOW(), NOW());
INSERT INTO `config` VALUES (23, 'Oss', 'AwsS3AccessId', '', '亚马逊S3AccessID', '亚马逊S3AccessID', NOW(), NOW());
INSERT INTO `config` VALUES (24, 'Oss', 'AwsS3AccessKey', '', '亚马逊S3AccessKey', '亚马逊S3AccessKey', NOW(), NOW());
INSERT INTO `config` VALUES (25, 'Oss', 'AwsS3Endpoint', '', '亚马逊S3端点', '亚马逊S3端点', NOW(), NOW());
INSERT INTO `config` VALUES (26, 'Oss', 'AwsS3Region', '', '亚马逊S3地区', '亚马逊S3地区', NOW(), NOW());
INSERT INTO `config` VALUES (27, 'Oss', 'AwsS3Bucket', '', '亚马逊S3桶名', '亚马逊S3桶名', NOW(), NOW());
INSERT INTO `config` VALUES (28, 'Oss', 'AwsS3Host', '', '亚马逊S3域名', '亚马逊S3域名', NOW(), NOW());
