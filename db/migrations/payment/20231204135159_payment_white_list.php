<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PaymentWhiteList extends AbstractMigration
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
-- Table structure for payment_white_list
-- ----------------------------
DROP TABLE IF EXISTS `payment_white_list`;
CREATE TABLE `payment_white_list`  (
  `paymentWhiteId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paymentPlatformId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付平台id',
  `platformObj` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '平台类名',
  `ip` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '白名单ip',
  `ipLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '对应的ipLong',
  PRIMARY KEY (`paymentWhiteId`) USING BTREE,
  INDEX `ipLong`(`ipLong`) USING BTREE,
  INDEX `paymentObj`(`platformObj`) USING BTREE,
  INDEX `paymentPlatformId`(`paymentPlatformId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '支付渠道白名单表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `payment_white_list`');
    }
}
