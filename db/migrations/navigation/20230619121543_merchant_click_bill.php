<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MerchantClickBill extends AbstractMigration
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
-- Table structure for merchant_click_bill
-- ----------------------------
DROP TABLE IF EXISTS `merchant_click_bill`;
CREATE TABLE `merchant_click_bill`  (
  `merchantId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id',
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '所属日期',
  `count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计数（笔数）',
  `amount` decimal(16, 4) NOT NULL DEFAULT 0.0000 COMMENT '此次帐变金额',
  `settlement` enum('Completed','Pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Pending' COMMENT '结算状态\r\n1.Completed 已完成的\r\n2.Pending 待处理的',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`merchantId`, `date`) USING BTREE,
  INDEX `merchantId`(`merchantId`) USING BTREE,
  INDEX `date`(`date`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '商户点击计费表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of merchant_click_bill
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
        $this->execute('DROP TABLE IF EXISTS `merchant_click_bill`');
    }
}
