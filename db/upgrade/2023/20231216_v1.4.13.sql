-- 直播详情中不要绑定广告id了
ALTER TABLE `live` DROP COLUMN `adId`;

-- 增加直播详情中的文字广告位
INSERT INTO `nav_ad_group` VALUES (64, '性浪直播详情页文字', '', 'xlLiveText', '[]', 40, 1, NOW(), NOW());
INSERT INTO `nav_page_template_zone_relation` VALUES (104, 10, 33, 64, 1, 40);
