
-- 增加龍晟支付
INSERT INTO `payment_platform` VALUES (3, '龍晟支付', 'LongSheng', '{\"mchId\":\"971086\",\"createOrderUrl\":\"http://pay.8longsheng.com/pay/v1/order\",\"secretKey\":\"49113c570856e54300b0ba71e1c63625c95229ee5a00cb4d\"}', 1, NOW(), NOW());

INSERT INTO `payment_white_list` VALUES (6, 3, 'LongSheng', '47.242.238.173', 804449965);

INSERT INTO `payment_channel` VALUES (10, 3, 1, '优质支付宝', '', 10, 1000, '{\"payType\":8001}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (11, 3, 1, '支付宝旗舰店H5', '', 50, 200, '{\"payType\":8007}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (12, 3, 1, '支付宝小芒电商', '支付宝电商(100,200)', 100, 200, '{\"payType\":8011}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (13, 3, 1, '支付宝直播', '', 10, 2000, '{\"payType\":8021}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (14, 3, 2, '哇塞微信', '', 10, 500, '{\"payType\":8000}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (15, 3, 2, '微信财付通', '微信财付通(100,200)', 100, 200, '{\"payType\":8006}', 0, 0, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (16, 3, 2, '微信原生H5', '微信原生H5(100,200)', 100, 500, '{\"payType\":8022}', 0, 0, NOW(), NOW());

