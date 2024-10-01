
-- 修改用户表的索引
ALTER TABLE `user`
DROP INDEX `phoneCountryCode`,
DROP INDEX `phoneNumber`,
ADD INDEX(`phoneNumber`, `phoneCountryCode`);


-- 注意新增了表
-- payment_white_list
-- user_vip_goods
-- user_vip_order
-- payment_platform
-- payment_type
-- payment_channel

-- 添加用户所在分组的过期时间
ALTER TABLE `user`
    ADD COLUMN `userGroupExpiryDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '用户组有效时间' AFTER `userGroupId`;

-- 增加Payment的组
ALTER TABLE `config`
    MODIFY COLUMN `configGroup` enum('Test','System','WebSite','Navigation','App','Video','Oss','JSMS','Payment') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Test' COMMENT '配置分组\r\n测试 Test\r\n系统 System\r\n网站 WebSite\r\n导航业务 Navigation\r\n移动应用 App\r\n视频 Video\r\n存储 Oss\r\n极光短信 JSMS\r\n支付 Payment' AFTER `configId`;

-- 调整一下索引结构
ALTER TABLE `config`
DROP INDEX `cfgKey`,
ADD UNIQUE INDEX `cfgKey`(`cfgKey`, `configGroup`) USING BTREE;



-- ATTENTION 这个是第三方支付要加的数据，其他项目不需要哈
-- INSERT INTO `payment_white_list` VALUES (1, '天汇', 'TianHui', '167.179.88.227', 2813548771);
-- INSERT INTO `payment_white_list` VALUES (2, '天汇', 'TianHui', '158.247.208.201', 2667040969);
-- INSERT INTO `payment_white_list` VALUES (3, '天汇', 'TianHui', '192.243.127.164', 3237183396);
-- INSERT INTO `payment_white_list` VALUES (4, '天汇', 'TianHui', '116.213.38.186', 1960126138);

-- payment_channel
INSERT INTO `payment_channel` VALUES (1, 1, 1, '支付宝综合原生-H5', '', 10, 1000, '{\"wayCode\":49}', 20, 1, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (2, 1, 1, '支付宝超级原生', '', 10, 2000, '{\"wayCode\":537}', 30, 1, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (3, 1, 1, '支付宝快手', '', 10, 2000, '{\"wayCode\":1050}', 40, 1, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (4, 1, 1, '支付宝YY', '', 10, 500, '{\"wayCode\":1060}', 50, 1, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (5, 1, 1, '支付宝扫码转账', '', 50, 10000, '{\"wayCode\":1081}', 10, 1, NOW(), NOW());
INSERT INTO `payment_channel` VALUES (6, 1, 2, '微信超级原生', '', 10, 500, '{\"wayCode\":10030}', 60, 1, NOW(), NOW());

-- payment_platform
INSERT INTO `payment_platform` VALUES (1, '天汇', 'TianHui', 1, NOW(), NOW());


