-- 加直播诱导位 短视频诱导位
INSERT INTO `nav_ad_group` VALUES (66, '直播诱导位', '', 'xlLiveInduce', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (67, '短视频诱导位', '', 'xlShortInduce', '[]', 10, 1, NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (106, 10, 33, 66, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (107, 10, 32, 67, 1, 10);

