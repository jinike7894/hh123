<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Admin extends AbstractMigration
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
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `adminId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentAdminId` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级管理员id',
  `roleId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '角色id，多个角色用逗号分割',
  `merchantId` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id',
  `adminNickname` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理员昵称（同样是商户昵称，会在前台显示）',
  `adminAccount` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理员登录名',
  `adminPassword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理员密码',
  `adminType` enum('System','Merchant') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'System' COMMENT '管理员类型',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户头像（同样是商户头像）',
  `adminEmail` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理员邮箱',
  `adminMobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号码',
  `lastLoginIpLong` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录IP',
  `lastLoginTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '最后登录时间',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '用户状态 -1删除 0禁用 1正常',
  `googleAuthenticatorSecret` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'google验证码秘钥',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`adminId`) USING BTREE,
  INDEX `adminAccount`(`adminAccount`) USING BTREE,
  INDEX `parentAdminId`(`parentAdminId`) USING BTREE,
  INDEX `roleId`(`roleId`) USING BTREE,
  INDEX `merchantId`(`merchantId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '管理员列表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 0, '1', 0, 'admin', 'admin', '$2y$10$ruzwOjKOkbWepvFP6UwrIOHhEcCJHEuWxxiUjcO8o2I68IlrPbYAW', 'System', '', '', '', 2886860801, '1000-01-01 00:00:00', 1, '', NOW(), NOW());
INSERT INTO `admin` VALUES (2, 0, '1', 0, 'tommy', 'tommy', '$2y$10$teHTrZCvrdmDi.GiKfU3LOnUbOr01oFB0y7czNVHl5PXWxZhIKPoi', 'System', '', '', '', 2886860801, '1000-01-01 00:00:00', 1, '', NOW(), NOW());
INSERT INTO `admin` VALUES (3, 0, '3', 1, 'mertommy', 'mertommy', '$2y$10$hIwl3Zwh.yfaaD2YkfS02.CSvt/IzbG4Z.AnW3RBgA/4PlIdfiuc2', 'Merchant', '', '', '', 2886860801, '1000-01-01 00:00:00', 1, '', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `admin`');
    }
}
