# 增加抹茶模板首页的应用列表从1个改成4个广告组，这里添加另外3个

UPDATE nav_page_template_zone_relation SET sort = 10 where pageTemplateZoneRelationId = 50;
update nav_ad_group set adGroupAlias = '推荐' where adGroupId = 23;

INSERT INTO `nav_ad_group` VALUES (32, '抹茶应用2（视频）', '视频', 'matchaAppListVideo', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (33, '抹茶应用3（直播）', '直播', 'matchaAppListLive', '[]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (34, '抹茶应用4（赚钱）', '赚钱', 'matchaAppListGame', '[]', NOW(), NOW());


INSERT INTO `nav_page_template_zone_relation` VALUES (68, 7, 15, 32, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (69, 7, 15, 33, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (70, 7, 15, 34, 1, 40);

INSERT INTO `nav_ad_group_relation` VALUES (32, 13, 10);
INSERT INTO `nav_ad_group_relation` VALUES (32, 14, 20);
INSERT INTO `nav_ad_group_relation` VALUES (33, 7, 10);
INSERT INTO `nav_ad_group_relation` VALUES (33, 8, 20);
INSERT INTO `nav_ad_group_relation` VALUES (34, 21, 10);
INSERT INTO `nav_ad_group_relation` VALUES (34, 22, 20);