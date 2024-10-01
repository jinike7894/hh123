-- 注意这里用的是共用的状态，但其实页面是没有删除状态的。
ALTER TABLE `nav_page`
    ADD COLUMN `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常' AFTER `latestTime`;

-- 配置增加 单个IP单广告每日点击次数统计限制
INSERT INTO `config` VALUES (33, 'Navigation', 'SingleIpDailyClickLimit', '1', '单个IP单广告每日点击次数统计限制', '0为不限制', NOW(), NOW());

-- 广告管理增加绑定商户，之前加过字段，但是没加索引。
ALTER TABLE `nav_ad`
    ADD INDEX(`merchantId`);

-- 渠道增加单价字段
ALTER TABLE `ch_channel`
    MODIFY COLUMN `merchantId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id' AFTER `channelId`,
    ADD COLUMN `cost` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '渠道单次安装价格' AFTER `percentage`,
    ADD COLUMN `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注' AFTER `cost`,
    ADD INDEX(`merchantId`);
