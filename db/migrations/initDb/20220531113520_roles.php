<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Roles extends AbstractMigration
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
-- Table structure for roles
-- ----------------------------
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles`  (
  `roleId` int(11) NOT NULL AUTO_INCREMENT,
  `roleName` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '角色名',
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '角色状态(0 删除 1 正常)',
  `remark` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '权限描述',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`roleId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of roles
-- ----------------------------
INSERT INTO `roles` VALUES (1, '超级管理员', 1, '这个未区分权限，默认不要用，只留一个就好。', '2022-03-22 16:20:47', '2022-05-17 14:03:55');
INSERT INTO `roles` VALUES (2, '总后台管理员', 1, '选角色的时候选这个不要选超级管理员。', '2022-05-17 14:02:11', '2022-05-17 14:04:31');
INSERT INTO `roles` VALUES (3, '商户总管理员', 1, '添加商户的时候默认这个角色。', '2022-05-17 14:02:13', '2022-05-17 14:03:41');

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `roles`');
    }
}
