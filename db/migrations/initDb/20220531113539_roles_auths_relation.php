<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RolesAuthsRelation extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = <<<sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for roles_auths_relation
-- ----------------------------
DROP TABLE IF EXISTS `roles_auths_relation`;
CREATE TABLE `roles_auths_relation`  (
  `roleId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '角色id',
  `authId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '权限id',
  PRIMARY KEY (`roleId`, `authId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色和权限的关系表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of roles_auths_relation
-- ----------------------------
INSERT INTO `roles_auths_relation` VALUES (2, 1);
INSERT INTO `roles_auths_relation` VALUES (2, 2);
INSERT INTO `roles_auths_relation` VALUES (2, 3);
INSERT INTO `roles_auths_relation` VALUES (2, 4);
INSERT INTO `roles_auths_relation` VALUES (2, 5);
INSERT INTO `roles_auths_relation` VALUES (2, 6);
INSERT INTO `roles_auths_relation` VALUES (2, 7);
INSERT INTO `roles_auths_relation` VALUES (2, 8);
INSERT INTO `roles_auths_relation` VALUES (2, 9);
INSERT INTO `roles_auths_relation` VALUES (2, 10);
INSERT INTO `roles_auths_relation` VALUES (2, 11);
INSERT INTO `roles_auths_relation` VALUES (2, 12);
INSERT INTO `roles_auths_relation` VALUES (2, 13);
INSERT INTO `roles_auths_relation` VALUES (2, 14);
INSERT INTO `roles_auths_relation` VALUES (2, 15);
INSERT INTO `roles_auths_relation` VALUES (2, 16);
INSERT INTO `roles_auths_relation` VALUES (2, 17);
INSERT INTO `roles_auths_relation` VALUES (2, 18);
INSERT INTO `roles_auths_relation` VALUES (2, 19);
INSERT INTO `roles_auths_relation` VALUES (2, 20);
INSERT INTO `roles_auths_relation` VALUES (2, 21);
INSERT INTO `roles_auths_relation` VALUES (2, 22);
INSERT INTO `roles_auths_relation` VALUES (2, 23);
INSERT INTO `roles_auths_relation` VALUES (2, 24);
INSERT INTO `roles_auths_relation` VALUES (2, 25);
INSERT INTO `roles_auths_relation` VALUES (2, 26);
INSERT INTO `roles_auths_relation` VALUES (2, 27);
INSERT INTO `roles_auths_relation` VALUES (2, 28);
INSERT INTO `roles_auths_relation` VALUES (2, 29);
INSERT INTO `roles_auths_relation` VALUES (2, 30);
INSERT INTO `roles_auths_relation` VALUES (2, 31);
INSERT INTO `roles_auths_relation` VALUES (2, 32);
INSERT INTO `roles_auths_relation` VALUES (2, 33);
INSERT INTO `roles_auths_relation` VALUES (2, 34);
INSERT INTO `roles_auths_relation` VALUES (2, 35);
INSERT INTO `roles_auths_relation` VALUES (2, 36);
INSERT INTO `roles_auths_relation` VALUES (2, 37);
INSERT INTO `roles_auths_relation` VALUES (2, 38);
INSERT INTO `roles_auths_relation` VALUES (2, 39);
INSERT INTO `roles_auths_relation` VALUES (2, 40);
INSERT INTO `roles_auths_relation` VALUES (2, 41);
INSERT INTO `roles_auths_relation` VALUES (2, 42);
INSERT INTO `roles_auths_relation` VALUES (2, 43);
INSERT INTO `roles_auths_relation` VALUES (2, 44);
INSERT INTO `roles_auths_relation` VALUES (2, 45);
INSERT INTO `roles_auths_relation` VALUES (2, 46);
INSERT INTO `roles_auths_relation` VALUES (2, 47);
INSERT INTO `roles_auths_relation` VALUES (2, 48);
INSERT INTO `roles_auths_relation` VALUES (2, 49);
INSERT INTO `roles_auths_relation` VALUES (2, 50);
INSERT INTO `roles_auths_relation` VALUES (2, 52);
INSERT INTO `roles_auths_relation` VALUES (2, 53);
INSERT INTO `roles_auths_relation` VALUES (2, 54);
INSERT INTO `roles_auths_relation` VALUES (2, 55);
INSERT INTO `roles_auths_relation` VALUES (2, 56);
INSERT INTO `roles_auths_relation` VALUES (2, 57);
INSERT INTO `roles_auths_relation` VALUES (2, 58);
INSERT INTO `roles_auths_relation` VALUES (2, 59);
INSERT INTO `roles_auths_relation` VALUES (2, 60);
INSERT INTO `roles_auths_relation` VALUES (2, 61);
INSERT INTO `roles_auths_relation` VALUES (2, 62);
INSERT INTO `roles_auths_relation` VALUES (2, 63);
INSERT INTO `roles_auths_relation` VALUES (2, 66);
INSERT INTO `roles_auths_relation` VALUES (2, 67);
INSERT INTO `roles_auths_relation` VALUES (2, 68);
INSERT INTO `roles_auths_relation` VALUES (2, 69);
INSERT INTO `roles_auths_relation` VALUES (2, 70);
INSERT INTO `roles_auths_relation` VALUES (2, 71);
INSERT INTO `roles_auths_relation` VALUES (2, 72);
INSERT INTO `roles_auths_relation` VALUES (2, 73);
INSERT INTO `roles_auths_relation` VALUES (2, 74);
INSERT INTO `roles_auths_relation` VALUES (2, 75);
INSERT INTO `roles_auths_relation` VALUES (3, 15);
INSERT INTO `roles_auths_relation` VALUES (3, 51);
INSERT INTO `roles_auths_relation` VALUES (3, 64);
INSERT INTO `roles_auths_relation` VALUES (3, 65);

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `roles_auths_relation`');
    }
}
