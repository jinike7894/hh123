ALTER TABLE `nav_ad_click_statistic`
    ADD COLUMN `h5ClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'h5点击数' AFTER `retainedClickCount`,
ADD COLUMN `appClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'app点击数' AFTER `h5ClickCount`;