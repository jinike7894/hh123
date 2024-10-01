<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserVipOrder extends AbstractMigration
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
-- Table structure for user_vip_order
-- ----------------------------
DROP TABLE IF EXISTS `user_vip_order`;
CREATE TABLE `user_vip_order`  (
  `orderId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '订单id',
  `orderNo` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '订单号',
  `userId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '买家id',
  `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单来源页面id',
  `channelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '订单来源渠道id',
  `paymentTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付类型id',
  `paymentChannelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '支付渠道id',
  `goodsId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商品id',
  `amount` decimal(10, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品价格',
  `status` enum('WaitingBuyersPayment','BuyerCancelsPayment','BuyerPaymentTimeout','OrderCompleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'WaitingBuyersPayment' COMMENT '状态\r\nWaitingBuyersPayment 等待买家付款\r\nBuyerCancelsPayment 买家取消付款\r\nBuyerPaymentTimeout 买家付款超时\r\nOrderCompleted 订单完成',
  `createDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '创建日期',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `finishDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '完成日期',
  `finishTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '完成时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`orderId`) USING BTREE,
  INDEX `userId`(`userId`) USING BTREE,
  INDEX `pageId`(`pageId`) USING BTREE,
  INDEX `channelId`(`channelId`) USING BTREE,
  INDEX `orderNo`(`orderNo`) USING BTREE,
  INDEX `createTime`(`createTime`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户开会员订单表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `user_vip_order`');
    }
}
