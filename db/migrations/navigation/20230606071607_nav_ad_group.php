<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavAdGroup extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = <<<'sql'

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for nav_ad_group
-- ----------------------------
DROP TABLE IF EXISTS `nav_ad_group`;
CREATE TABLE `nav_ad_group`  (
  `adGroupId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adGroupName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告组名',
  `adGroupAlias` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告组别名（显示名字）',
  `adGroupKey` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告组识别key',
  `extensionFields` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '扩展字段配置，默认为空数组，一定要有默认值。',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（正序）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`adGroupId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 68 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_ad_group
-- ----------------------------
INSERT INTO `nav_ad_group` VALUES (1, 'sm-顶部浮动', '', 'topFloat', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (2, 'sm-横幅', '', 'banner', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (3, 'sm-tab热门', '热门', 'tabHot', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (4, 'sm-tab视频', '视频', 'tabVideo', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (5, 'sm-tab直播', '直播', 'tabLive', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (6, 'sm-tab游戏', '游戏', 'tabGame', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (7, 'sm-推荐', '下载推荐', 'recommend', '[{\"name\":\"标签\",\"key\":\"tag\"},{\"name\":\"下载次数\",\"key\":\"times\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (8, 'sm-约会', '', 'date', '[{\"name\":\"标签1\",\"key\":\"tag1\"},{\"name\":\"标签2\",\"key\":\"tag2\"},{\"name\":\"标签3\",\"key\":\"tag3\"},{\"name\":\"年龄\",\"key\":\"age\"},{\"name\":\"身高\",\"key\":\"height\"},{\"name\":\"尺寸\",\"key\":\"cup\"},{\"name\":\"地区\",\"key\":\"district\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (9, 'sm-底部浮动', '', 'bottomFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (10, 'sm-tab赚钱', '赚钱', 'tabMakeMoney', '[{\"name\":\"右下脚标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"描述（红字）\",\"key\":\"descriptionRed\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (11, '51顶部浮动', '', '51topFloat', '[{\"name\":\"公告\",\"key\":\"announcement\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (12, '51顶部横幅', '', '51topBanner', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (13, '51推荐', '热门', '51recommend', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (14, '51横幅', '', '51banner', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (15, '51下载', '', '51download', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"下载量\",\"key\":\"downloads\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (16, '51底部浮动', '', '51bottomFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (17, '51顶部浮动T1', '', '51topFloatT1', '[{\"name\":\"公告\",\"key\":\"announcement\"},{\"name\":\"切换按钮的链接\",\"key\":\"link\"},{\"name\":\"切换按钮的文字\",\"key\":\"buttonText\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (18, '51列表2（视频）', '视频', '51tabVideo', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (19, '51列表3（直播）', '直播', '51tabLive', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (20, '51列表4（游戏）', '游戏', '51tabGame', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (21, '抹茶logo', '', 'matchaLogo', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"公告\",\"key\":\"announcement\"},{\"name\":\"下载链接\",\"key\":\"link\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (22, '抹茶横幅', '', 'matchaBanner', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (23, '抹茶应用', '热门', 'matchaAppList', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (24, '抹茶App启动图', '', 'matchaAppLaunch', '[{\"name\":\"倒计时秒数\",\"key\":\"countdown\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (25, '抹茶弹窗图', '', 'matchaDialog', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (26, '抹茶视频图', '', 'matchaVideo', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (27, '抹茶分享图', '', 'matchaShare', '[{\"name\":\"链接\",\"key\":\"link\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (28, '51顶部下载浮动', '', '51TopAppFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"按钮文字\",\"key\":\"buttonText\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (29, '3图单页', '', '3picturesGroup', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (30, '抹茶首页楼凤专区', '', 'matchaDateZone', '[{\"name\":\"文本1\",\"key\":\"text1\"},{\"name\":\"文本2\",\"key\":\"text2\"},{\"name\":\"数字范围，2个数逗号隔开\",\"key\":\"range\"},{\"name\":\"名字\",\"key\":\"name\"},{\"name\":\"年龄\",\"key\":\"age\"},{\"name\":\"项目\",\"key\":\"project\"},{\"name\":\"方式\",\"key\":\"mode\"},{\"name\":\"介绍\",\"key\":\"introduction\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (31, '抹茶首页直播专区', '', 'matchaLiveZone', '[{\"name\":\"标签\",\"key\":\"tag\"},{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"文本1\",\"key\":\"text1\"},{\"name\":\"数字范围，2个数逗号隔开\",\"key\":\"range\"},{\"name\":\"名字\",\"key\":\"name\"},{\"name\":\"介绍\",\"key\":\"introduction\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (32, '抹茶应用2（视频）', '视频', 'matchaAppListVideo', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (33, '抹茶应用3（直播）', '直播', 'matchaAppListLive', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (34, '抹茶应用4（赚钱）', '赚钱', 'matchaAppListGame', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (35, '抹茶推荐列表', '热门推荐', 'matchaRecommendedList', '[{\"name\":\"描述\",\"key\":\"description\"},{\"name\":\"下载量\",\"key\":\"downloads\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (36, '抹茶楼凤详情顶部浮动', '', 'matchaGirlTopFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (37, '抹茶楼凤详情底部浮动', '', 'matchaGirlBottomFloat', '[{\"name\":\"标题\",\"key\":\"title\"},{\"name\":\"描述\",\"key\":\"description\"}]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (38, '抹茶楼凤详情其他推荐', '', 'matchaGirlRecommendedList', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (39, '抹茶成人列表嵌入', '', 'matchaAdultListInsertion', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (40, '抹茶横幅2（小）', '', 'matchaBannerSmall', '[]', 0, 0, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (41, '性浪首页logo', '', 'xlHomeLogo', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (42, '性浪首页banner', '', 'xlHomeBanner', '[]', 20, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (43, '性浪首页九宫格', '', 'xlHomeApp', '[]', 30, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (44, '性浪首页视频列表嵌入', '', 'xlHomeVideoInsertion', '[]', 40, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (45, '性浪AVBanner', '', 'xlAVBanner', '[]', 70, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (46, '性浪AV九宫格', '', 'xlAVApp', '[]', 80, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (47, '性浪AV视频列表嵌入', '', 'xlAVVideoInsertion', '[]', 90, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (48, '性浪AV详情观看广告', '', 'xlAVDetailDuration', '[{\"name\":\"持续时间（秒）\",\"key\":\"duration\"}]', 100, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (49, '性浪AV详情返回广告', '', 'xlAVDetailReturn', '[]', 110, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (50, '性浪楼凤Banner', '', 'xlProstituteBanner', '[]', 120, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (51, '性浪楼凤九宫格', '', 'xlProstituteApp', '[]', 130, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (52, '性浪楼凤列表嵌入', '', 'xlProstituteListInsertion', '[]', 140, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (53, '性浪楼凤分类跳转', '', 'xlProstituteType', '[]', 150, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (54, '性浪短视频列表嵌入', '', 'xlShortVideoInsertion', '[]', 160, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (55, '性浪短视频漂浮', '', 'xlShortVideoFloat', '[]', 170, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (56, '性浪App启动图', '', 'xlHomeAppLaunch', '[{\"name\":\"倒计时秒数\",\"key\":\"countdown\"}]', 50, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (57, '性浪首页弹窗图', '', 'xlHomeDialog', '[]', 60, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (58, '性浪楼凤列表嵌入1', '', 'xlProstituteListInsertion1', '[]', 141, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (59, '性浪楼凤列表嵌入2', '', 'xlProstituteListInsertion2', '[]', 142, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (60, '性浪首页文字列表', '', 'xlHomeTextList', '[]', 50, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (61, '性浪直播Banner', '', 'xlLiveBanner', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (62, '性浪直播列表嵌入', '', 'xlLiveInsertion', '[]', 20, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (63, '性浪直播详情页漂浮', '', 'xlLiveFloat', '[]', 30, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (64, '性浪直播详情页文字', '', 'xlLiveText', '[]', 40, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (65, '性浪我的页应用列表', '', 'xlMyInfoApp', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (66, '直播诱导位', '', 'xlLiveInduce', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (67, '短视频诱导位', '', 'xlShortInduce', '[]', 10, 1, NOW(), NOW());
INSERT INTO `nav_ad_group` VALUES (68, '性浪游戏Banner', '', 'xlGameBanner', '[]', 10, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_ad_group`');
    }
}
