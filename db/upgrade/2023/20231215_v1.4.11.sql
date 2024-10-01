
-- 批量审核这两天陈老师视频资源推送的视频数据
UPDATE `mac_vod` SET `vod_status` = 1 WHERE `vod_play_from` = 'dplayer' AND `vod_time_add` > "1702396800";

-- 批量分配这两天陈老师视频资源推送的短视频类型数据为抖音类型
UPDATE `mac_vod` SET `type_id` = 62 WHERE `vod_play_from` = 'dplayer' AND `vod_time_add` > "1702396800" AND `vod_class` = "短视频" AND `type_id` = 0;


-- 添加直播模块广告
INSERT INTO `nav_zone` VALUES (33, '性浪直播', 'xlLive', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (60, '性浪首页文字列表', '', 'xlHomeTextList', '[]', 50, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (61, '性浪直播Banner', '', 'xlLiveBanner', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (62, '性浪直播列表嵌入', '', 'xlLiveInsertion', '[]', 20, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (63, '性浪直播详情页漂浮', '', 'xlLiveFloat', '[]', 30, 1, NOW(), NOW());


INSERT INTO `nav_page_template_zone_relation` VALUES (100, 10, 29, 60, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (101, 10, 33, 61, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (102, 10, 33, 62, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (103, 10, 33, 63, 1, 30);

