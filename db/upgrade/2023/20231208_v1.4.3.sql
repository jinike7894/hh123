ALTER TABLE `user_vip_goods`
    ADD COLUMN `goodsType` enum('Common','NewUser') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Common' COMMENT '商品类型' AFTER `goodsKey`;

INSERT INTO `user_vip_goods` VALUES (5, 'Forever', 'NewUser', '新人限时永久卡', '新人限时永久卡', 300.00, 100.00, 36500, 0, 1, NOW(), NOW());


-- 重新弄一下白名单表
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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

INSERT INTO `payment_white_list` VALUES (1, 1, 'TianHui', '167.179.88.227', 2813548771);
INSERT INTO `payment_white_list` VALUES (2, 1, 'TianHui', '158.247.208.201', 2667040969);
INSERT INTO `payment_white_list` VALUES (3, 1, 'TianHui', '192.243.127.164', 3237183396);
INSERT INTO `payment_white_list` VALUES (4, 1, 'TianHui', '116.213.38.186', 1960126138);
