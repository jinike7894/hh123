-- 加主公告配置
INSERT INTO `config` VALUES (40, 'WebSite', 'MainAnnouncement', '主公告', '主公告', '主公告', NOW(), NOW());

-- 加“我的”页面广告位
INSERT INTO `nav_zone` VALUES (34, '性浪我的', 'xlMyInfo', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (65, '性浪我的页应用列表', '', 'xlMyInfoApp', '[]', 10, 1, NOW(), NOW());

INSERT INTO `nav_page_template_zone_relation` VALUES (105, 10, 34, 65, 1, 10);
