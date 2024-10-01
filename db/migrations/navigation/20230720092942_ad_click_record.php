<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdClickRecord extends AbstractMigration
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
-- Table structure for nav_ad_click_record
-- ----------------------------
DROP TABLE IF EXISTS `nav_ad_click_record`;
CREATE TABLE `nav_ad_click_record`  (
  `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '页面id',
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
  `deviceId` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '设备id',
  `ipLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ipLong值',
  `screen` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '屏幕宽高',
  `adId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告id',
  `ip` char(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'ip字符串',
  `clickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击计数',
  `firstTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '第一次点击时间',
  `latestTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '最后一次点击时间',
  PRIMARY KEY (`pageId`, `date`, `deviceId`, `ipLong`, `screen`, `adId`) USING BTREE,
  INDEX `date`(`date`) USING BTREE,
  INDEX `ipLong`(`ipLong`) USING BTREE,
  INDEX `screen`(`screen`) USING BTREE,
  INDEX `adId`(`adId`) USING BTREE,
  INDEX `clickCount`(`clickCount`) USING BTREE,
  INDEX `deviceId`(`deviceId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告点击记录表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_ad_click_record`');
    }
}
