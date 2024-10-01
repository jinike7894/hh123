-- 支付平台表加上key之类的数据
ALTER TABLE `payment_platform`
    ADD COLUMN `platformData` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '平台数据' AFTER `platformObj`,
ADD INDEX(`platformObj`);

TRUNCATE TABLE `payment_platform`;
INSERT INTO `payment_platform` VALUES (1, '天汇', 'TianHui', '{\"mchId\":\"M1700826768\",\"createOrderUrl\":\"https://tianhuipaye8ervv.vbcc.xyz/api/pay/unifiedorder\",\"secretKey\":\"9e5d3cd428d24654a39a19c1f515dfb7\"}', 1, NOW(), NOW());
INSERT INTO `payment_platform` VALUES (2, 'YK支付', 'YKPayment', '{\"mchId\":\"M1702038682011074\",\"createOrderUrl\":\"http://47.236.100.90:8089/channel/apiPay\",\"secretKey\":\"7613166c14614a6f95ecc59512d861d7\"}', 1, NOW(), NOW());

-- 增加白名单
INSERT INTO `payment_white_list` VALUES (5, 2, 'YKPayment', '47.236.100.90', 804021338);

-- 增加支付渠道
INSERT INTO `payment_channel` VALUES (null, 2, 1, '支付宝-YK', '', 10, 1000, '{\"tradeType\":\"ALIPAY_H5\"}', 0, 1, NOW(), NOW());

