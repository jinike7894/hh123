<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Config extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = <<<sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for config
-- ----------------------------
DROP TABLE IF EXISTS `config`;
CREATE TABLE `config`  (
  `configId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `configGroup` enum('Test','System','WebSite','Navigation','App','Video','Oss','JSMS','Payment','Other','Live') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n移动应用 App\r\n视频 Video\r\n存储 Oss\r\n极光短信 JSMS\r\n支付 Payment\r\n其他 Other\r\n直播 Live',
  `cfgKey` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置项键',
  `cfgValue` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置值',
  `title` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置标题',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '配置项描述',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`configId`) USING BTREE,
  UNIQUE INDEX `cfgKey`(`cfgKey`, `configGroup`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '全站配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of config
-- ----------------------------
INSERT INTO `config` VALUES (1, 'System', 'WebsiteMaintenance', '{\"status\":\"0\",\"content\":\"系统维护中\"}', '平台维护(0 未维护, 1维护)', '平台维护(0 未维护, 1维护)', NOW(), NOW());
INSERT INTO `config` VALUES (2, 'System', 'ForceSingleDeviceLogin', '0', '强制单设备登录', '1.是 0.否', NOW(), NOW());
INSERT INTO `config` VALUES (3, 'System', 'AppVersion', '{\"currentVersion\":\"0.1\",\"minVersion\":\"0.1\",\"packageAddress\":\"\",\"updatePackageAddress\":\"\",\"domain\":\"\"}', '版本号和下载包配置', '版本号和下载包配置', NOW(), NOW());
INSERT INTO `config` VALUES (4, 'WebSite', 'WebsiteTitle', 'ES导航', '网站标题', 'title 的值', NOW(), NOW());
INSERT INTO `config` VALUES (5, 'WebSite', 'WebsiteKeywords', 'ES导航关键字', '网站关键字', 'meta keywords 的值', NOW(), NOW());
INSERT INTO `config` VALUES (6, 'WebSite', 'WebsiteDescription', 'ES导航描述', '网站描述', 'meta description 的值', NOW(), NOW());
INSERT INTO `config` VALUES (7, 'WebSite', 'WebsiteContact', '广告联系TG:XX', '联系人', '网站联系人', NOW(), NOW());
INSERT INTO `config` VALUES (8, 'WebSite', 'CDN', '', 'cdn地址', 'cdn地址', NOW(), NOW());
INSERT INTO `config` VALUES (9, 'Navigation', 'MerchantBalanceReminder', '0', '商户余额提醒开关', '是否开启余额不足提醒 1.是 0.否', NOW(), NOW());
INSERT INTO `config` VALUES (10, 'Navigation', 'ReminderAmount', '200', '商户余额提醒金额', '低于这个数就会提醒', NOW(), NOW());
INSERT INTO `config` VALUES (11, 'Navigation', 'ReminderFrequency', '60', '商户余额提醒频率', '单位秒', NOW(), NOW());
INSERT INTO `config` VALUES (12, 'WebSite', 'Favicon', '', '网站图标', '网站图标', NOW(), NOW());
INSERT INTO `config` VALUES (13, 'App', 'AndroidVersion', '', '安卓当前版本', '安卓当前版本', NOW(), NOW());
INSERT INTO `config` VALUES (14, 'App', 'AndroidMinVersion', '', '安卓最低支持版本', '安卓最低支持版本', NOW(), NOW());
INSERT INTO `config` VALUES (15, 'App', 'AndroidDownloadUrl', '', '安卓下载地址', '安卓下载地址', NOW(), NOW());
INSERT INTO `config` VALUES (16, 'App', 'IOSVersion', '', 'IOS当前版本', 'IOS当前版本', NOW(), NOW());
INSERT INTO `config` VALUES (17, 'App', 'IOSMinVersion', '', 'IOS最低支持版本', 'IOS最低支持版本', NOW(), NOW());
INSERT INTO `config` VALUES (18, 'App', 'IOSDownloadUrl', '', 'IOS下载地址', 'IOS下载地址', NOW(), NOW());
INSERT INTO `config` VALUES (19, 'App', 'ApiDomain', '', '接口地址列表', '多个用;号隔开，最后一个不用。', NOW(), NOW());
INSERT INTO `config` VALUES (20, 'App', 'DownloadPageUrl', '', '下载页地址', '下载页地址', NOW(), NOW());
INSERT INTO `config` VALUES (21, 'Video', 'VideoApi_mdm3u8', '', '真不卡接口地址', '真不卡接口地址', NOW(), NOW());
INSERT INTO `config` VALUES (22, 'Oss', 'AwsS3Enabled', '0', '亚马逊S3启用', '1.是 0.否', NOW(), NOW());
INSERT INTO `config` VALUES (23, 'Oss', 'AwsS3AccessId', '', '亚马逊S3AccessID', '亚马逊S3AccessID', NOW(), NOW());
INSERT INTO `config` VALUES (24, 'Oss', 'AwsS3AccessKey', '', '亚马逊S3AccessKey', '亚马逊S3AccessKey', NOW(), NOW());
INSERT INTO `config` VALUES (25, 'Oss', 'AwsS3Endpoint', '', '亚马逊S3端点', '亚马逊S3端点', NOW(), NOW());
INSERT INTO `config` VALUES (26, 'Oss', 'AwsS3Region', '', '亚马逊S3地区', '亚马逊S3地区', NOW(), NOW());
INSERT INTO `config` VALUES (27, 'Oss', 'AwsS3Bucket', '', '亚马逊S3桶名', '亚马逊S3桶名', NOW(), NOW());
INSERT INTO `config` VALUES (28, 'Oss', 'AwsS3Host', '', '亚马逊S3域名', '亚马逊S3域名', NOW(), NOW());
INSERT INTO `config` VALUES (29, 'WebSite', 'WebsiteStatisticEnabled', '0', '网站统计扣量控制开关', '1.开 0关', NOW(), NOW());
INSERT INTO `config` VALUES (30, 'WebSite', 'WebsiteStatisticConfig', '0#59#100;60#1439#90;', '网站统计扣量控制配置', '格式：开始分钟#结束分钟#统计百分比;', NOW(), NOW());
INSERT INTO `config` VALUES (31, 'WebSite', 'WebsiteCustomerService', '', '网站客服联系地址', '网站客服联系地址', NOW(), NOW());
INSERT INTO `config` VALUES (32, 'WebSite', 'WebsiteContactGroup', '', '网站联系群组地址', '网站联系群组地址', NOW(), NOW());
INSERT INTO `config` VALUES (33, 'Navigation', 'SingleIpDailyClickLimit', '1', '单个IP单广告每日点击次数统计限制', '0为不限制', NOW(), NOW());
INSERT INTO `config` VALUES (34, 'JSMS', 'JSAppKey', '', '极光短信Key', '极光短信Key', NOW(), NOW());
INSERT INTO `config` VALUES (35, 'JSMS', 'JSMasterSecret', '', '极光短信MasterSecret', '极光短信MasterSecret', NOW(), NOW());
INSERT INTO `config` VALUES (36, 'JSMS', 'JSTemplateId', '', '极光短信模板Id', '极光短信模板Id', NOW(), NOW());
INSERT INTO `config` VALUES (37, 'JSMS', 'JSIpLimit', '100', '极光短信Ip每日次数限制', '极光短信Ip每日次数限制', NOW(), NOW());
INSERT INTO `config` VALUES (38, 'App', 'H5PageUrl', '', 'H5页面地址', 'H5页面地址', NOW(), NOW());
INSERT INTO `config` VALUES (39, 'Live', 'Announcement', '直播公告', '直播公告', '直播公告', NOW(), NOW());
INSERT INTO `config` VALUES (40, 'WebSite', 'MainAnnouncement', '主公告', '主公告', '主公告', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `config`');
    }
}
