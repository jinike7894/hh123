ALTER TABLE `ch_channel`
    DROP INDEX `channelKey`,
    ADD COLUMN `channelDomain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道域名' AFTER `channelKey`,
    ADD INDEX(`channelDomain`),
    ADD UNIQUE INDEX `channelKey`(`channelKey`) USING BTREE;

ALTER TABLE `ch_channel_install_statistic`
    CHANGE COLUMN `count` `installAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓虚假安装计数' AFTER `channelId`,
    CHANGE COLUMN `realCount` `realInstallAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓真实安装计数' AFTER `installAndroid`,
    CHANGE COLUMN `active` `activeAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓虚假活跃计数' AFTER `realInstallAndroid`,
    CHANGE COLUMN `realActive` `realActiveAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓真实活跃计数' AFTER `activeAndroid`;

ALTER TABLE `ch_channel_install_statistic`
    ADD COLUMN `installIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS虚假安装计数' AFTER `realActiveAndroid`,
    ADD COLUMN `realInstallIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS真实安装计数' AFTER `installIOS`,
    ADD COLUMN `activeIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS虚假活跃计数' AFTER `realInstallIOS`,
    ADD COLUMN `realActiveIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS真实活跃计数' AFTER `activeIOS`,
    ADD COLUMN `installIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签虚假安装计数' AFTER `realActiveIOS`,
    ADD COLUMN `realInstallIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签真实安装计数' AFTER `installIOSBookmark`,
    ADD COLUMN `activeIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签虚假活跃计数' AFTER `realInstallIOSBookmark`,
    ADD COLUMN `realActiveIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签真实活跃计数' AFTER `activeIOSBookmark`,
    ADD COLUMN `installTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计虚假安装计数' AFTER `realActiveIOSBookmark`,
    ADD COLUMN `realInstallTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计真实安装计数' AFTER `installTotal`,
    ADD COLUMN `activeTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计虚假活跃计数' AFTER `realInstallTotal`,
    ADD COLUMN `realActiveTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计真实活跃计数' AFTER `activeTotal`;

# 刷新统计数据
update ch_channel_install_statistic set installTotal = installAndroid + installIOS + installIOSBookmark,
    realInstallTotal = realInstallAndroid + realInstallIOS + realInstallIOSBookmark,
    activeTotal = activeAndroid + activeIOS + activeIOSBookmark,
    realActiveTotal = realActiveAndroid + realActiveIOS + realActiveIOSBookmark;

INSERT INTO `auths` VALUES (74, 57, '渠道总计列表', '/Api/Admin/Merchant/Channel/statisticTotalListSystem', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (75, 57, '渠道总计图表数据', '/Api/Admin/Merchant/Channel/statisticTotalChartSystem', '', '', 0, 0, '', NOW(), NOW());

INSERT INTO `roles_auths_relation` VALUES (2, 74);
INSERT INTO `roles_auths_relation` VALUES (2, 75);
