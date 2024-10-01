
-- 增加极光短信配置
ALTER TABLE `config`
    MODIFY COLUMN `configGroup` enum('Test','System','WebSite','Navigation','App','Video','Oss','JSMS') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n移动应用 App\r\n视频 Video\r\n存储 Oss\r\n极光短信 JSMS' AFTER `configId`;

INSERT INTO `config` VALUES (34, 'JSMS', 'JSAppKey', 'fc2b4193f9775f9b0519590d', '极光短信Key', '极光短信Key', NOW(), NOW());
INSERT INTO `config` VALUES (35, 'JSMS', 'JSMasterSecret', 'c30e0c013d9246e233adb44f', '极光短信MasterSecret', '极光短信MasterSecret', NOW(), NOW());
INSERT INTO `config` VALUES (36, 'JSMS', 'JSTemplateId', '1', '极光短信模板Id', '极光短信模板Id', NOW(), NOW());
INSERT INTO `config` VALUES (37, 'JSMS', 'JSIpLimit', '100', '极光短信Ip每日次数限制', '极光短信Ip每日次数限制', NOW(), NOW());


-- 增加用户表的来源关系字段
ALTER TABLE `user`
    ADD COLUMN `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册来源页面id' AFTER `password`,
ADD COLUMN `channelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册来源渠道id' AFTER `pageId`,
ADD INDEX(`pageId`),
ADD INDEX(`channelId`);

-- 把以前的数据设置一个默认对应的渠道，根据线上的数据来设置。
-- UPDATE `user` SET pageId = 1, channelId = 1 WHERE pageId = 0 AND channelId = 0;

