
-- 添加一些首页的分类
INSERT INTO `mac_type` VALUES (48, '首页福利', 'shouyefuli', 0, 16, 0, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (49, '热门', 'remen', 10, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (50, '麻豆', 'madou', 20, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (51, '九一', 'jiuyi', 30, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (52, '皇家', 'huangjia', 40, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (53, '蜜桃', 'mitao', 50, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (54, '精东', 'jingdong', 60, 16, 48, 1, '', '', '', '', '', '', '', '', '', NULL, '', '', '');


-- 增加 广告分组的状态，方便去掉一些不需要的
ALTER TABLE `nav_ad_group`
    ADD COLUMN `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除' AFTER `extensionFields`;


-- 增加模板 性浪
INSERT INTO `nav_page_template` VALUES (10, '性浪', 'xingLang', '性浪', NOW(), NOW());

INSERT INTO `nav_zone` VALUES (29, '性浪首页', 'xlHome', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (30, '性浪AV', 'xlAdultVideo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (31, '性浪楼凤', 'xlProstitute', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (32, '性浪短视频', 'xlShortVideo', NOW(), NOW());

INSERT INTO `nav_ad_group` VALUES (41, '性浪首页logo', '', 'xlHomeLogo', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (42, '性浪首页banner', '', 'xlHomeBanner', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (43, '性浪首页九宫格', '', 'xlHomeApp', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (44, '性浪首页视频列表嵌入', '', 'xlHomeVideoInsertion', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (45, '性浪AVBanner', '', 'xlAVBanner', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (46, '性浪AV九宫格', '', 'xlAVApp', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (47, '性浪AV视频列表嵌入', '', 'xlAVVideoInsertion', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (48, '性浪AV详情观看广告', '', 'xlAVDetailDuration', '[{\"name\":\"持续时间（秒）\",\"key\":\"duration\"}]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (49, '性浪AV详情返回广告', '', 'xlAVDetailReturn', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (50, '性浪楼凤Banner', '', 'xlProstituteBanner', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (51, '性浪楼凤九宫格', '', 'xlProstituteApp', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (52, '性浪楼凤列表嵌入', '', 'xlProstituteListInsertion', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (53, '性浪楼凤分类跳转', '', 'xlProstituteType', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (54, '性浪短视频列表嵌入', '', 'xlShortVideoInsertion', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (55, '性浪短视频漂浮', '', 'xlShortVideoFloat', '[]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (56, '性浪App启动图', '', 'xlHomeAppLaunch', '[{\"name\":\"倒计时秒数\",\"key\":\"countdown\"}]', 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (57, '性浪首页弹窗图', '', 'xlHomeDialog', '[]', 1, NOW(), NOW());


INSERT INTO `nav_page_template_zone_relation` VALUES (81, 10, 29, 41, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (82, 10, 29, 42, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (83, 10, 29, 43, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (84, 10, 29, 44, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (85, 10, 30, 45, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (86, 10, 30, 46, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (87, 10, 30, 47, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (88, 10, 30, 48, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (89, 10, 30, 49, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (90, 10, 31, 50, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (91, 10, 31, 51, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (92, 10, 31, 52, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (93, 10, 31, 53, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (94, 10, 32, 54, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (95, 10, 32, 55, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (96, 10, 29, 56, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (97, 10, 29, 57, 1, 60);
