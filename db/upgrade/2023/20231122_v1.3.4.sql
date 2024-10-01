ALTER TABLE `nav_ad_group`
    ADD COLUMN `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（正序）' AFTER `extensionFields`;


INSERT INTO `nav_ad_group` VALUES (58, '性浪楼凤列表嵌入1', '', 'xlProstituteListInsertion1', '[]', 141, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (59, '性浪楼凤列表嵌入2', '', 'xlProstituteListInsertion2', '[]', 142, 1, NOW(), NOW());


INSERT INTO `nav_page_template_zone_relation` VALUES (98, 10, 31, 58, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (99, 10, 31, 59, 1, 60);