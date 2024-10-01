<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class User extends AbstractMigration
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
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `userId` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `userGroupId` smallint(5) UNSIGNED NOT NULL DEFAULT 1 COMMENT '用户组id',
  `userGroupExpiryDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '用户组有效时间',
  `userName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '登录密码',
  `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册来源页面id',
  `channelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册来源渠道id',
  `phoneCountryCode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号国家编码',
  `phoneNumber` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '手机号',
  `deviceId` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '设备id',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `balance` decimal(12, 2) NOT NULL DEFAULT 0.00 COMMENT '余额',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '头像（可以是id也可以是图片地址，看功能）',
  `regIpLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '注册ipLong',
  `regDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '注册日期',
  `lastLoginIpLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最后登录IPLong',
  `lastLoginTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '最后登录时间',
  `remark` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`userId`) USING BTREE,
  UNIQUE INDEX `deviceId`(`deviceId`) USING BTREE,
  INDEX `userName`(`userName`) USING BTREE,
  INDEX `pageId`(`pageId`) USING BTREE,
  INDEX `channelId`(`channelId`) USING BTREE,
  INDEX `phoneNumber`(`phoneNumber`, `phoneCountryCode`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 100001 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `user`');
    }
}
