INSERT INTO `nav_ad_group` VALUES (11, '51顶部浮动', '', '51topFloat', '[{\"name\":\"公告\",\"key\":\"announcement\"}]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (12, '51顶部横幅', '', '51topBanner', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (13, '51推荐', '', '51recommend', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (14, '51横幅', '', '51banner', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (15, '51下载', '', '51download', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"下载量\",\"key\":\"downloads\"}]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (16, '51底部浮动', '', '51bottomFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', NOW(), NOW());

INSERT INTO `nav_page_template` VALUES (4, '51导航', '51Navigation', '51导航的模板样式', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (28, 4, 7, 11, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (29, 4, 8, 12, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (30, 4, 9, 13, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (31, 4, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (32, 4, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (33, 4, 12, 16, 1, 0);

INSERT INTO `nav_zone` VALUES (7, '51顶部浮动', '51topFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (8, '51顶部横幅', '51topBanner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (9, '51推荐', '51recommend', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (10, '51横幅', '51banner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (11, '51下载', '51download', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (12, '51底部浮动', '51bottomFloat', NOW(), NOW());
