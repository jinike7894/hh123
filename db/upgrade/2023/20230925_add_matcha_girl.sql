# 修改抹茶楼凤广告组的一些属性

UPDATE nav_ad_group SET extensionFields = '[{\"name\":\"文本1\",\"key\":\"text1\"},{\"name\":\"文本2\",\"key\":\"text2\"},{\"name\":\"数字范围，2个数逗号隔开\",\"key\":\"range\"},{\"name\":\"名字\",\"key\":\"name\"},{\"name\":\"年龄\",\"key\":\"age\"},{\"name\":\"项目\",\"key\":\"project\"},{\"name\":\"方式\",\"key\":\"mode\"},{\"name\":\"介绍\",\"key\":\"introduction\"}]' WHERE adGroupId = 30;
UPDATE nav_ad_group SET extensionFields = '[{\"name\":\"标签\",\"key\":\"tag\"},{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"文本1\",\"key\":\"text1\"},{\"name\":\"数字范围，2个数逗号隔开\",\"key\":\"range\"},{\"name\":\"名字\",\"key\":\"name\"},{\"name\":\"介绍\",\"key\":\"introduction\"}]' WHERE adGroupId = 31;

# 然后增加楼凤详情页面的广告位

INSERT INTO `nav_zone` VALUES (24, '抹茶楼凤详情顶部浮动', 'matchaGirlTopFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (25, '抹茶楼凤详情底部浮动', 'matchaGirlBottomFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (26, '抹茶楼凤详情其他推荐', 'matchaGirlRecommendedList', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (36, '抹茶楼凤详情顶部浮动', '', 'matchaGirlTopFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (37, '抹茶楼凤详情底部浮动', '', 'matchaGirlBottomFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (38, '抹茶楼凤详情其他推荐', '', 'matchaGirlRecommendedList', '[]', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (72, 7, 24, 36, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (73, 7, 25, 37, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (74, 7, 26, 38, 1, 0);

INSERT INTO `nav_ad_group_relation` VALUES (36, 40, 10);
INSERT INTO `nav_ad_group_relation` VALUES (37, 40, 10);
INSERT INTO `nav_ad_group_relation` VALUES (38, 4, 10);
INSERT INTO `nav_ad_group_relation` VALUES (38, 5, 20);
INSERT INTO `nav_ad_group_relation` VALUES (38, 6, 30);
INSERT INTO `nav_ad_group_relation` VALUES (38, 7, 40);
INSERT INTO `nav_ad_group_relation` VALUES (38, 8, 50);
INSERT INTO `nav_ad_group_relation` VALUES (38, 9, 60);
INSERT INTO `nav_ad_group_relation` VALUES (38, 10, 70);
INSERT INTO `nav_ad_group_relation` VALUES (38, 11, 80);