ALTER TABLE `nav_ad`
    ADD COLUMN `adTypeId` smallint UNSIGNED NOT NULL DEFAULT 1 COMMENT '广告分类id' AFTER `adId`;

# 添加了nav_ad_type表
DROP TABLE IF EXISTS `nav_ad_type`;
CREATE TABLE `nav_ad_type`  (
    `adTypeId` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
    `adTypeName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型名',
    `conversionRate` decimal(5, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '转化率百分比0-100',
    `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
    `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
    `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`adTypeId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告分类表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_ad_type
-- ----------------------------
INSERT INTO `nav_ad_type` VALUES (1, '默认', 100.00, 1, NOW(), NOW());
INSERT INTO `nav_ad_type` VALUES (2, '播放器', 20.00, 1, NOW(), NOW());
INSERT INTO `nav_ad_type` VALUES (3, '直播', 10.00, 1, NOW(), NOW());
INSERT INTO `nav_ad_type` VALUES (4, '炮台', 2.00, 1, NOW(), NOW());
INSERT INTO `nav_ad_type` VALUES (5, '博彩', 2.00, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

# 注意要改前面所有的广告插入SQL
