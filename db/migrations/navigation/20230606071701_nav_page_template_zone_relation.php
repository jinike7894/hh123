<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavPageTemplateZoneRelation extends AbstractMigration
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
-- Table structure for nav_page_template_zone_relation
-- ----------------------------
DROP TABLE IF EXISTS `nav_page_template_zone_relation`;
CREATE TABLE `nav_page_template_zone_relation`  (
  `pageTemplateZoneRelationId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageTemplateId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '页面模板id',
  `zoneId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告位id',
  `adGroupId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '关联的广告组id',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `sort` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '多个广告组间的排序，单广告组则不需要。正序排列。',
  PRIMARY KEY (`pageTemplateZoneRelationId`) USING BTREE,
  UNIQUE INDEX `unique_pt_z_ag`(`pageTemplateId`, `zoneId`, `adGroupId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 108 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '页面模板与广告位关联表，带有广告位与广告组的关联信息。' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_page_template_zone_relation
-- ----------------------------
INSERT INTO `nav_page_template_zone_relation` VALUES (1, 1, 1, 1, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (2, 1, 2, 2, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (3, 1, 3, 3, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (4, 1, 3, 4, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (5, 1, 3, 5, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (6, 1, 3, 6, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (7, 1, 4, 7, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (8, 1, 5, 8, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (9, 1, 6, 9, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (10, 2, 1, 1, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (11, 2, 2, 2, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (12, 2, 3, 3, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (13, 2, 3, 4, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (14, 2, 3, 5, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (15, 2, 3, 10, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (16, 2, 4, 7, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (17, 2, 5, 8, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (18, 2, 6, 9, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (19, 3, 1, 1, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (20, 3, 2, 2, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (21, 3, 3, 3, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (22, 3, 3, 4, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (23, 3, 3, 5, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (24, 3, 3, 6, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (25, 3, 4, 7, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (26, 3, 5, 8, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (27, 3, 6, 9, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (28, 4, 7, 11, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (29, 4, 8, 12, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (30, 4, 9, 13, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (31, 4, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (32, 4, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (33, 4, 12, 16, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (34, 5, 7, 17, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (35, 5, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (36, 5, 9, 13, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (37, 5, 9, 18, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (38, 5, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (39, 5, 12, 16, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (40, 6, 7, 17, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (41, 6, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (42, 6, 9, 13, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (43, 6, 9, 18, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (44, 6, 9, 19, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (45, 6, 9, 20, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (46, 6, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (47, 6, 12, 16, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (48, 7, 13, 21, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (49, 7, 14, 22, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (50, 7, 15, 23, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (51, 7, 16, 24, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (52, 7, 17, 25, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (53, 7, 18, 26, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (54, 7, 19, 27, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (55, 6, 20, 28, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (56, 8, 7, 17, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (57, 8, 10, 14, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (58, 8, 9, 13, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (59, 8, 9, 18, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (60, 8, 9, 19, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (61, 8, 9, 20, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (62, 8, 11, 15, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (63, 8, 12, 16, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (64, 8, 20, 28, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (65, 9, 21, 29, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (66, 7, 22, 30, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (67, 7, 22, 31, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (68, 7, 15, 32, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (69, 7, 15, 33, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (70, 7, 15, 34, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (71, 7, 23, 35, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (72, 7, 24, 36, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (73, 7, 25, 37, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (74, 7, 26, 38, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (75, 7, 27, 39, 1, 0);
INSERT INTO `nav_page_template_zone_relation` VALUES (76, 6, 28, 30, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (77, 6, 28, 31, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (78, 8, 28, 30, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (79, 8, 28, 31, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (80, 7, 14, 40, 1, 20);
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
INSERT INTO `nav_page_template_zone_relation` VALUES (98, 10, 31, 58, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (99, 10, 31, 59, 1, 60);
INSERT INTO `nav_page_template_zone_relation` VALUES (100, 10, 29, 60, 1, 50);
INSERT INTO `nav_page_template_zone_relation` VALUES (101, 10, 33, 61, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (102, 10, 33, 62, 1, 20);
INSERT INTO `nav_page_template_zone_relation` VALUES (103, 10, 33, 63, 1, 30);
INSERT INTO `nav_page_template_zone_relation` VALUES (104, 10, 33, 64, 1, 40);
INSERT INTO `nav_page_template_zone_relation` VALUES (105, 10, 34, 65, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (106, 10, 33, 66, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (107, 10, 32, 67, 1, 10);
INSERT INTO `nav_page_template_zone_relation` VALUES (108, 10, 35, 68, 1, 10);

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_page_template_zone_relation`');
    }
}
