<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MerchantBalanceChange extends AbstractMigration
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
-- Table structure for merchant_balance_change
-- ----------------------------
DROP TABLE IF EXISTS `merchant_balance_change`;
CREATE TABLE `merchant_balance_change`  (
  `merchantBalanceChangeId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `merchantId` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id',
  `type` enum('ManualAdd','ManualReduce','Click') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '账变类型\r\n1.调整加币 + ManualAdd\r\n2.调整减币 - ManualReduce\r\n3.点击计费 - Click',
  `amount` decimal(16, 4) NOT NULL DEFAULT 0.0000 COMMENT '此次帐变金额',
  `preBalance` decimal(16, 4) NOT NULL DEFAULT 0.0000 COMMENT '帐变前余额',
  `newBalance` decimal(16, 4) NOT NULL DEFAULT 0.0000 COMMENT '帐变后余额',
  `remark` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `createDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '创建日期',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`merchantBalanceChangeId`) USING BTREE,
  INDEX `merchantId`(`merchantId`) USING BTREE,
  INDEX `createDate`(`createDate`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户余额变化记录表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of merchant_balance_change
-- ----------------------------
INSERT INTO `merchant_balance_change` VALUES (1, 1, 'ManualAdd', 10.0000, 0.0000, 10.0000, '管理员[tommy]操作。', NOW(), NOW(), NOW());
INSERT INTO `merchant_balance_change` VALUES (2, 1, 'ManualReduce', -5.0000, 10.0000, 5.0000, '管理员[tommy]操作。', NOW(), NOW(), NOW());
INSERT INTO `merchant_balance_change` VALUES (3, 1, 'ManualReduce', -5.0000, 5.0000, 0.0000, '管理员[tommy]操作。', NOW(), NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `merchant_balance_change`');
    }
}
