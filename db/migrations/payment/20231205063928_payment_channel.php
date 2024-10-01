<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PaymentChannel extends AbstractMigration
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
-- Table structure for payment_channel
-- ----------------------------
DROP TABLE IF EXISTS `payment_channel`;
CREATE TABLE `payment_channel`  (
  `paymentChannelId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `paymentPlatformId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '平台id',
  `paymentTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型id',
  `channelName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道名',
  `channelAlias` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道别名，别名如果存在则作为显示值',
  `min` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最小限额',
  `max` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最大限额',
  `params` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道参数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（正叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`paymentChannelId`) USING BTREE,
  INDEX `paymentPlatformId`(`paymentPlatformId`) USING BTREE,
  INDEX `paymentTypeId`(`paymentTypeId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '支付渠道表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `payment_channel`');
    }
}
