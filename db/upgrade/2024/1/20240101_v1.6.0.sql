CREATE TABLE `nav_landpage_statistic`  (
   `channelKey` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '渠道key',
   `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
   `ip` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ip统计',
   PRIMARY KEY (`channelKey`, `date`) USING BTREE,
   INDEX `channelKey`(`channelKey`) USING BTREE,
   INDEX `date`(`date`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '落地页统计表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_landpage_statistic
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;