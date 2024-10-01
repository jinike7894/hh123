<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavZone extends AbstractMigration
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
-- Table structure for nav_zone
-- ----------------------------
DROP TABLE IF EXISTS `nav_zone`;
CREATE TABLE `nav_zone`  (
  `zoneId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `zoneName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告位名',
  `zoneKey` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告位key',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`zoneId`) USING BTREE,
  INDEX `zoneKey`(`zoneKey`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 35 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告位表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_zone
-- ----------------------------
INSERT INTO `nav_zone` VALUES (1, '顶部浮动', 'topFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (2, '横幅', 'banner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (3, '标签页', 'tab', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (4, '推荐', 'recommend', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (5, '约会', 'date', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (6, '底部浮动', 'bottomFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (7, '51顶部浮动', '51topFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (8, '51顶部横幅', '51topBanner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (9, '51推荐', '51recommend', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (10, '51横幅', '51banner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (11, '51下载', '51download', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (12, '51底部浮动', '51bottomFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (13, '抹茶logo', 'matchaLogo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (14, '抹茶横幅', 'matchaBanner', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (15, '抹茶应用', 'matchaAppList', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (16, '抹茶App启动图', 'matchaAppLaunch', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (17, '抹茶弹窗图', 'matchaDialog', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (18, '抹茶视频图', 'matchaVideo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (19, '抹茶分享图', 'matchaShare', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (20, '51顶部下载浮动', '51topAppFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (21, '3图单页', '3picturesZone', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (22, '抹茶首页专区', 'matchaHomeZone', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (23, '抹茶推荐列表', 'matchaRecommend', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (24, '抹茶楼凤详情顶部浮动', 'matchaGirlTopFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (25, '抹茶楼凤详情底部浮动', 'matchaGirlBottomFloat', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (26, '抹茶楼凤详情其他推荐', 'matchaGirlRecommendedList', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (27, '抹茶成人列表嵌入', 'matchaAdultListInsertion', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (28, '51首页专区', '51HomeZone', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (29, '性浪首页', 'xlHome', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (30, '性浪AV', 'xlAdultVideo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (31, '性浪楼凤', 'xlProstitute', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (32, '性浪短视频', 'xlShortVideo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (33, '性浪直播', 'xlLive', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (34, '性浪我的', 'xlMyInfo', NOW(), NOW());
INSERT INTO `nav_zone` VALUES (35, '性浪游戏', 'xlGame', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_zone`');
    }
}
