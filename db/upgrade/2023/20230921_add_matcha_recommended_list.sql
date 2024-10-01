# 增加抹茶模板中热门推荐广告组

INSERT INTO `nav_ad_group` VALUES (35, '抹茶推荐列表', '热门推荐', 'matchaRecommendedList', '[{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"下载量\",\"key\":\"downloads\"}]', NOW(), NOW());

INSERT INTO `nav_zone` VALUES (23, '抹茶推荐列表', 'matchaRecommendedList', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (71, 7, 23, 35, 1, 0);
