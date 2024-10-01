# 新增抹茶的承认列表嵌入 广告位

INSERT INTO `nav_zone` VALUES (27, '抹茶成人列表嵌入', 'matchaAdultListInsertion', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (39, '抹茶成人列表嵌入', '', 'matchaAdultListInsertion', '[]', NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (75, 7, 27, 39, 1, 0);


