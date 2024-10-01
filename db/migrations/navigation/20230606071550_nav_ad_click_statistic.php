<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavAdClickStatistic extends AbstractMigration
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
-- Table structure for nav_ad_click_statistic
-- ----------------------------
DROP TABLE IF EXISTS `nav_ad_click_statistic`;
CREATE TABLE `nav_ad_click_statistic`  (
  `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '页面id',
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
  `adId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '广告id',
  `clickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击计数',
  `clickIpCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击ip计数',
  `retainedClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '留存用户点击计数',
  `h5ClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'h5点击数',
  `appClickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'app点击数',
  `totalCost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT '费用总计',
  PRIMARY KEY (`pageId`, `date`, `adId`) USING BTREE,
  INDEX `pageId`(`pageId`) USING BTREE,
  INDEX `date`(`date`) USING BTREE,
  INDEX `adId`(`adId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告点击统计表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_ad_click_statistic
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_ad_click_statistic`');
    }
}
