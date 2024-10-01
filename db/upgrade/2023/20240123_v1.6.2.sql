-- 加游戏banner广告位
INSERT INTO `nav_ad_group` VALUES (68, '性浪游戏Banner', '', 'xlGameBanner', '[]', 10, 1, NOW(), NOW());

-- 加“游戏”页面广告位
INSERT INTO `nav_zone` VALUES (35, '性浪游戏', 'xlGame', NOW(), NOW());

-- 性浪不执行，种子视频执行
INSERT INTO `config` VALUES (41, 'WebSite', 'GameNotify', '游戏跑马灯通知', '游戏跑马灯通知', '游戏跑马灯通知', NOW(), NOW());


INSERT INTO `nav_page_template_zone_relation` VALUES (108, 10, 35, 68, 1, 10);

