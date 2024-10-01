-- 增加明宇支付
INSERT INTO `payment_platform` VALUES (4, '名宇支付', 'MingYu', '{\"mchId\":\"20000029\",\"createOrderUrl\":\"https://wg.yazy.xyz/api/pay/create_order\",\"secretKey\":\"80D41SKATSK7M63F3DOXM05FOQNEBIBJZNSRUSXTQSG9LGOYH13MFF0OQKHJWPOFTE6U5QIB1E85PNTGFLPPGZBDFUJX3CGWXF43E908Z9XJ9LDZMGJOAAILWTMWXBOK\"}', 1, NOW(), NOW());

INSERT INTO `payment_white_list` VALUES (7, 4, 'MingYu', '34.85.120.208', 576026832);

INSERT INTO `payment_channel` VALUES (17, 4, 1, '支付宝-MingYu', '', 30, 300, '{\"productId\":8008}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (18, 4, 2, '微信-MingYu', '', 30, 300, '{\"productId\":8007}', 0, 0, NOW(), NOW());
