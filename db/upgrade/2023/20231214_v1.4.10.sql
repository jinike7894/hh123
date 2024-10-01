
-- 现在短视频的数据都没了，直接清空表，重新弄数据。
TRUNCATE TABLE mac_short_vod

-- 增加直播的有个公告配置
ALTER TABLE `config` MODIFY COLUMN `configGroup` enum('Test','System','WebSite','Navigation','App','Video','Oss','JSMS','Payment','Other','Live') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n移动应用 App\r\n视频 Video\r\n存储 Oss\r\n极光短信 JSMS\r\n支付 Payment\r\n其他 Other\r\n直播 Live' AFTER `configId`;

INSERT INTO `config` VALUES (39, 'Live', 'Announcement', '直播公告', '直播公告', '直播公告', NOW(), NOW());
