<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavAdGroupRelation extends AbstractMigration
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
-- Table structure for nav_ad_group_relation
-- ----------------------------
DROP TABLE IF EXISTS `nav_ad_group_relation`;
CREATE TABLE `nav_ad_group_relation`  (
  `adGroupId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告组id',
  `adId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告id',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 100 COMMENT '这个广告在这个组的排序，正序排列',
  PRIMARY KEY (`adGroupId`, `adId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告与广告组关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_ad_group_relation
-- ----------------------------
INSERT INTO `nav_ad_group_relation` VALUES (1, 1, 100);
INSERT INTO `nav_ad_group_relation` VALUES (2, 2, 10);
INSERT INTO `nav_ad_group_relation` VALUES (2, 3, 20);
INSERT INTO `nav_ad_group_relation` VALUES (3, 4, 10);
INSERT INTO `nav_ad_group_relation` VALUES (3, 5, 20);
INSERT INTO `nav_ad_group_relation` VALUES (3, 6, 30);
INSERT INTO `nav_ad_group_relation` VALUES (3, 7, 40);
INSERT INTO `nav_ad_group_relation` VALUES (3, 8, 50);
INSERT INTO `nav_ad_group_relation` VALUES (3, 9, 60);
INSERT INTO `nav_ad_group_relation` VALUES (3, 10, 70);
INSERT INTO `nav_ad_group_relation` VALUES (3, 11, 80);
INSERT INTO `nav_ad_group_relation` VALUES (4, 12, 10);
INSERT INTO `nav_ad_group_relation` VALUES (4, 13, 20);
INSERT INTO `nav_ad_group_relation` VALUES (4, 14, 30);
INSERT INTO `nav_ad_group_relation` VALUES (4, 15, 40);
INSERT INTO `nav_ad_group_relation` VALUES (4, 16, 50);
INSERT INTO `nav_ad_group_relation` VALUES (4, 17, 60);
INSERT INTO `nav_ad_group_relation` VALUES (4, 18, 70);
INSERT INTO `nav_ad_group_relation` VALUES (5, 19, 10);
INSERT INTO `nav_ad_group_relation` VALUES (5, 20, 20);
INSERT INTO `nav_ad_group_relation` VALUES (6, 21, 10);
INSERT INTO `nav_ad_group_relation` VALUES (6, 22, 20);
INSERT INTO `nav_ad_group_relation` VALUES (6, 23, 30);
INSERT INTO `nav_ad_group_relation` VALUES (6, 24, 40);
INSERT INTO `nav_ad_group_relation` VALUES (6, 25, 50);
INSERT INTO `nav_ad_group_relation` VALUES (6, 26, 60);
INSERT INTO `nav_ad_group_relation` VALUES (7, 27, 10);
INSERT INTO `nav_ad_group_relation` VALUES (7, 28, 20);
INSERT INTO `nav_ad_group_relation` VALUES (7, 29, 30);
INSERT INTO `nav_ad_group_relation` VALUES (7, 30, 40);
INSERT INTO `nav_ad_group_relation` VALUES (7, 31, 50);
INSERT INTO `nav_ad_group_relation` VALUES (7, 32, 60);
INSERT INTO `nav_ad_group_relation` VALUES (8, 33, 10);
INSERT INTO `nav_ad_group_relation` VALUES (8, 34, 20);
INSERT INTO `nav_ad_group_relation` VALUES (8, 35, 30);
INSERT INTO `nav_ad_group_relation` VALUES (8, 36, 40);
INSERT INTO `nav_ad_group_relation` VALUES (8, 37, 50);
INSERT INTO `nav_ad_group_relation` VALUES (8, 38, 60);
INSERT INTO `nav_ad_group_relation` VALUES (8, 39, 70);
INSERT INTO `nav_ad_group_relation` VALUES (9, 40, 100);
INSERT INTO `nav_ad_group_relation` VALUES (11, 41, 100);
INSERT INTO `nav_ad_group_relation` VALUES (12, 42, 100);
INSERT INTO `nav_ad_group_relation` VALUES (13, 43, 10);
INSERT INTO `nav_ad_group_relation` VALUES (13, 44, 20);
INSERT INTO `nav_ad_group_relation` VALUES (13, 45, 30);
INSERT INTO `nav_ad_group_relation` VALUES (13, 46, 40);
INSERT INTO `nav_ad_group_relation` VALUES (13, 47, 50);
INSERT INTO `nav_ad_group_relation` VALUES (13, 48, 60);
INSERT INTO `nav_ad_group_relation` VALUES (13, 49, 70);
INSERT INTO `nav_ad_group_relation` VALUES (13, 50, 80);
INSERT INTO `nav_ad_group_relation` VALUES (13, 51, 90);
INSERT INTO `nav_ad_group_relation` VALUES (13, 52, 100);
INSERT INTO `nav_ad_group_relation` VALUES (13, 53, 110);
INSERT INTO `nav_ad_group_relation` VALUES (13, 54, 120);
INSERT INTO `nav_ad_group_relation` VALUES (14, 55, 10);
INSERT INTO `nav_ad_group_relation` VALUES (14, 56, 20);
INSERT INTO `nav_ad_group_relation` VALUES (14, 57, 30);
INSERT INTO `nav_ad_group_relation` VALUES (15, 58, 10);
INSERT INTO `nav_ad_group_relation` VALUES (15, 59, 20);
INSERT INTO `nav_ad_group_relation` VALUES (15, 60, 30);
INSERT INTO `nav_ad_group_relation` VALUES (15, 61, 40);
INSERT INTO `nav_ad_group_relation` VALUES (15, 62, 50);
INSERT INTO `nav_ad_group_relation` VALUES (15, 63, 60);
INSERT INTO `nav_ad_group_relation` VALUES (15, 64, 70);
INSERT INTO `nav_ad_group_relation` VALUES (15, 65, 80);
INSERT INTO `nav_ad_group_relation` VALUES (16, 66, 100);
INSERT INTO `nav_ad_group_relation` VALUES (21, 67, 100);
INSERT INTO `nav_ad_group_relation` VALUES (22, 2, 10);
INSERT INTO `nav_ad_group_relation` VALUES (22, 3, 20);
INSERT INTO `nav_ad_group_relation` VALUES (23, 4, 10);
INSERT INTO `nav_ad_group_relation` VALUES (23, 5, 20);
INSERT INTO `nav_ad_group_relation` VALUES (23, 6, 30);
INSERT INTO `nav_ad_group_relation` VALUES (23, 7, 40);
INSERT INTO `nav_ad_group_relation` VALUES (23, 8, 50);
INSERT INTO `nav_ad_group_relation` VALUES (23, 9, 60);
INSERT INTO `nav_ad_group_relation` VALUES (23, 10, 70);
INSERT INTO `nav_ad_group_relation` VALUES (23, 11, 80);
INSERT INTO `nav_ad_group_relation` VALUES (23, 12, 90);
INSERT INTO `nav_ad_group_relation` VALUES (23, 13, 100);
INSERT INTO `nav_ad_group_relation` VALUES (23, 14, 110);
INSERT INTO `nav_ad_group_relation` VALUES (23, 15, 120);
INSERT INTO `nav_ad_group_relation` VALUES (23, 16, 130);
INSERT INTO `nav_ad_group_relation` VALUES (23, 17, 140);
INSERT INTO `nav_ad_group_relation` VALUES (23, 18, 150);
INSERT INTO `nav_ad_group_relation` VALUES (23, 19, 160);
INSERT INTO `nav_ad_group_relation` VALUES (24, 2, 100);
INSERT INTO `nav_ad_group_relation` VALUES (25, 2, 100);
INSERT INTO `nav_ad_group_relation` VALUES (26, 2, 100);
INSERT INTO `nav_ad_group_relation` VALUES (27, 68, 100);
INSERT INTO `nav_ad_group_relation` VALUES (30, 69, 10);
INSERT INTO `nav_ad_group_relation` VALUES (30, 70, 20);
INSERT INTO `nav_ad_group_relation` VALUES (30, 71, 30);
INSERT INTO `nav_ad_group_relation` VALUES (30, 72, 40);
INSERT INTO `nav_ad_group_relation` VALUES (31, 73, 10);
INSERT INTO `nav_ad_group_relation` VALUES (31, 74, 20);
INSERT INTO `nav_ad_group_relation` VALUES (31, 75, 30);
INSERT INTO `nav_ad_group_relation` VALUES (31, 76, 40);
INSERT INTO `nav_ad_group_relation` VALUES (32, 13, 10);
INSERT INTO `nav_ad_group_relation` VALUES (32, 14, 20);
INSERT INTO `nav_ad_group_relation` VALUES (33, 7, 10);
INSERT INTO `nav_ad_group_relation` VALUES (33, 8, 20);
INSERT INTO `nav_ad_group_relation` VALUES (34, 21, 10);
INSERT INTO `nav_ad_group_relation` VALUES (34, 22, 20);
INSERT INTO `nav_ad_group_relation` VALUES (35, 15, 10);
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
INSERT INTO `nav_ad_group_relation` VALUES (40, 77, 10);
INSERT INTO `nav_ad_group_relation` VALUES (40, 78, 20);

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_ad_group_relation`');
    }
}
