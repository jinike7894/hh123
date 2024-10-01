<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Merchant extends AbstractMigration
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
-- Table structure for merchant
-- ----------------------------
DROP TABLE IF EXISTS `merchant`;
CREATE TABLE `merchant`  (
  `merchantId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商户id',
  `merchantName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商户名称',
  `balance` decimal(16, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT '余额',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`merchantId`) USING BTREE,
  INDEX `merchantName`(`merchantName`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户列表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of merchant
-- ----------------------------
INSERT INTO `merchant` VALUES (1, '测试商户', 0.0000, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `merchant`');
    }
}
