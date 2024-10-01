
-- 把单价的小数位数改为4位
ALTER TABLE `nav_page`
    MODIFY COLUMN `ipCost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT 'ip单价' AFTER `statisticConfig`;

ALTER TABLE `ch_channel`
    MODIFY COLUMN `cost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT '渠道单次安装价格' AFTER `percentage`;

-- 增加楼凤对于省市的关联
ALTER TABLE `p_prostitute`
    ADD COLUMN `provinceId` tinyint UNSIGNED NOT NULL DEFAULT 0 COMMENT '省级id' AFTER `contact`,
ADD COLUMN `cityId` smallint UNSIGNED NOT NULL DEFAULT 0 COMMENT '市级id' AFTER `provinceId`,
ADD INDEX(`provinceId`),
ADD INDEX(`cityId`);

