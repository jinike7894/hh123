
-- 添加九州支付
INSERT INTO `payment_platform` VALUES (5, '九州支付', 'JiuZhou', '{\"mchId\":\"231238676\",\"createOrderUrl\":\"http://bg.ddongkeji.com/Pay_index.html\",\"secretKey\":\"nl8qdclynimz3j3ukkgbig8c1vv159dq\"}', 1, NOW(), NOW());

INSERT INTO `payment_white_list` VALUES (8, 5, 'JiuZhou', '94.74.110.132', 1581936260);

INSERT INTO `payment_channel` VALUES (19, 5, 1, '九州支付宝', '', 30, 500, '{\"bankCode\":966}', 0, 0, NOW(), NOW());
