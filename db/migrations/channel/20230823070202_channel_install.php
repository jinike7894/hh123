<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChannelInstall extends AbstractMigration
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
-- Table structure for ch_channel_install
-- ----------------------------
DROP TABLE IF EXISTS `ch_channel_install`;
CREATE TABLE `ch_channel_install`  (
  `channelInstallId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '安装id',
  `channelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '渠道id',
  `source` enum('IOS','IOSBookmark','Android') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Android' COMMENT '来源',
  `ipLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ipLong值',
  `ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'ip字符串',
  `deviceId` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '设备id',
  `operatingSystem` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '系统名',
  `operatingSystemVersion` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '系统版本',
  `isCounted` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '1.计数 0.未计数，因为有扣量所以详情中要标记。',
  `latestActiveDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '最近活跃日期',
  `createDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '创建日期',
  `createTimeBucketHour` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间区间（小时）',
  `createTimeBucketHalfHour` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '时间区间（半小时）',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`channelInstallId`) USING BTREE,
  INDEX `ipLong`(`ipLong`) USING BTREE,
  INDEX `channelId`(`channelId`) USING BTREE,
  INDEX `createDate`(`createDate`) USING BTREE,
  INDEX `createTimeBucketHour`(`createTimeBucketHour`) USING BTREE,
  INDEX `createTimeBucketHalfHour`(`createTimeBucketHalfHour`) USING BTREE,
  INDEX `deviceId`(`deviceId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '渠道安装详情记录表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `ch_channel_install`');
    }
}
