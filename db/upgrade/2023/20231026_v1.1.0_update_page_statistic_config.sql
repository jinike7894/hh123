-- 页面表加入了统计扣量和ip单价的字段
ALTER TABLE `nav_page`
    ADD COLUMN `statisticEnabled` tinyint NOT NULL DEFAULT 0 COMMENT '统计代码控制 1.开 0.关' AFTER `description`,
ADD COLUMN `statisticConfig` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '统计代码控制配置' AFTER `statisticEnabled`,
ADD COLUMN `ipCost` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ip单价' AFTER `statisticConfig`;

-- 新增和抹茶的广告组和关联
INSERT INTO `nav_ad_group` VALUES (40, '抹茶横幅2（小）', '', 'matchaBannerSmall', '[]', NOW(), NOW());
INSERT INTO `nav_page_template_zone_relation` VALUES (80, 7, 14, 40, 1, 20);

-- 这个是修改了页面统计表加入了扣量后的ip
ALTER TABLE `nav_page_statistic`
    ADD COLUMN `reducedIp` int UNSIGNED NOT NULL DEFAULT 0 COMMENT '扣量后的ip统计' AFTER `ip`;

-- 增加了配置
INSERT INTO `config` VALUES (31, 'WebSite', 'WebsiteCustomerService', '', '网站客服联系地址', '网站客服联系地址', NOW(), NOW());
INSERT INTO `config` VALUES (32, 'WebSite', 'WebsiteContactGroup', '', '网站联系群组地址', '网站联系群组地址', NOW(), NOW());
