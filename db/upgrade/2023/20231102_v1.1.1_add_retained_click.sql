ALTER TABLE `nav_ad_click_statistic`
    ADD COLUMN `retainedClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '留存用户点击计数' AFTER `clickIpCount`;