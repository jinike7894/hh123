<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Channel extends AbstractMigration
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
-- Table structure for ch_channel
-- ----------------------------
DROP TABLE IF EXISTS `ch_channel`;
CREATE TABLE `ch_channel`  (
  `channelId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '渠道id',
  `merchantId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id',
  `channelKey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道key',
  `channelDomain` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道域名',
  `percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 100 COMMENT '计算百分比（比如扣量20%，就填80就好）',
  `cost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT '渠道单次安装价格',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`channelId`) USING BTREE,
  INDEX `channelDomain`(`channelDomain`) USING BTREE,
  INDEX `channelKey`(`channelKey`) USING BTREE,
  INDEX `merchantId`(`merchantId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '渠道表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of ch_channel
-- ----------------------------
INSERT INTO `ch_channel` VALUES (1, 1, 'index.html', '', 100, 0.0000, '默认渠道请勿删除', 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `ch_channel`');
    }
}
