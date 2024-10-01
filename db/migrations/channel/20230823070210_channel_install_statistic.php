<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChannelInstallStatistic extends AbstractMigration
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
-- Table structure for ch_channel_install_statistic
-- ----------------------------
DROP TABLE IF EXISTS `ch_channel_install_statistic`;
CREATE TABLE `ch_channel_install_statistic`  (
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
  `channelId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '渠道id',
  `installAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓虚假安装计数',
  `realInstallAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓真实安装计数',
  `activeAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓虚假活跃计数',
  `realActiveAndroid` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '安卓真实活跃计数',
  `installIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS虚假安装计数',
  `realInstallIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS真实安装计数',
  `activeIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS虚假活跃计数',
  `realActiveIOS` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS真实活跃计数',
  `installIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签虚假安装计数',
  `realInstallIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签真实安装计数',
  `activeIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签虚假活跃计数',
  `realActiveIOSBookmark` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'IOS书签真实活跃计数',
  `installTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计虚假安装计数',
  `realInstallTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计真实安装计数',
  `activeTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计虚假活跃计数',
  `realActiveTotal` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '总计真实活跃计数',
  PRIMARY KEY (`date`, `channelId`) USING BTREE,
  INDEX `channelId`(`channelId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '渠道安装统计表，只是缓存统计数据，可有可无。' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `ch_channel_install_statistic`');
    }
}
