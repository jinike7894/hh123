ALTER TABLE `config`
    MODIFY COLUMN `configGroup` enum('Test','System','WebSite','Navigation','App','Video') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n应用 App\r\n视频 Video' AFTER `configId`;

INSERT INTO `config` VALUES (13, 'App', 'AndroidVersion', '', '安卓当前版本', '安卓当前版本', NOW(), NOW());
INSERT INTO `config` VALUES (14, 'App', 'AndroidMinVersion', '', '安卓最低支持版本', '安卓最低支持版本', NOW(), NOW());
INSERT INTO `config` VALUES (15, 'App', 'AndroidDownloadUrl', '', '安卓下载地址', '安卓下载地址', NOW(), NOW());
INSERT INTO `config` VALUES (16, 'App', 'IOSVersion', '', 'IOS当前版本', 'IOS当前版本', NOW(), NOW());
INSERT INTO `config` VALUES (17, 'App', 'IOSMinVersion', '', 'IOS最低支持版本', 'IOS最低支持版本', NOW(), NOW());
INSERT INTO `config` VALUES (18, 'App', 'IOSDownloadUrl', '', 'IOS下载地址', 'IOS下载地址', NOW(), NOW());
INSERT INTO `config` VALUES (19, 'App', 'ApiDomain', '', '接口地址列表', '多个用;号隔开，最后一个不用。', NOW(), NOW());
INSERT INTO `config` VALUES (20, 'App', 'DownloadPageUrl', '', '下载页地址', '下载页地址', NOW(), NOW());
INSERT INTO `config` VALUES (21, 'Video', 'VideoApi_mdm3u8', '', '真不卡接口地址', '真不卡接口地址', NOW(), NOW());