UPDATE nav_ad_group SET adGroupAlias = '热门' WHERE adGroupKey = '51recommend';

INSERT INTO `nav_page_template` VALUES (5, '51导航1', '51Navigation1', '51导航2个列表', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (6, '51导航2', '51Navigation2', '51导航列表换tab', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (17, '51顶部浮动T1', '', '51topFloatT1', '[{\"name\":\"公告\",\"key\":\"announcement\"}]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (18, '51列表2（视频）', '视频', '51tabVideo', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (19, '51列表3（直播）', '直播', '51tabLive', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (20, '51列表4（游戏）', '游戏', '51tabGame', '[]', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (34, 5, 7, 17, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (35, 5, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (36, 5, 9, 13, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (37, 5, 9, 18, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (38, 5, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (39, 5, 12, 16, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (40, 6, 7, 17, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (41, 6, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (42, 6, 9, 13, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (43, 6, 9, 18, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (44, 6, 9, 19, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (45, 6, 9, 20, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (46, 6, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (47, 6, 12, 16, 1, 0);

-- 2023-08-24 补充
INSERT INTO `nav_zone` VALUES (20, '51顶部下载浮动', '51topAppFloat', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (55, 6, 20, 28, 1, 0);

INSERT INTO `nav_ad_group` VALUES (28, '51顶部下载浮动', '', '51TopAppFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', NOW(), NOW());

UPDATE nav_ad_group SET extensionFields = '[{\"name\":\"公告\",\"key\":\"announcement\"},{\"name\":\"切换按钮的链接\",\"key\":\"link\"}]' WHERE adGroupId = 17;

