<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavPageStatistic extends AbstractMigration
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
-- Table structure for nav_page_statistic
-- ----------------------------
DROP TABLE IF EXISTS `nav_page_statistic`;
CREATE TABLE `nav_page_statistic`  (
  `pageId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '页面id',
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
  `pv` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'pv统计',
  `ip` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ip统计',
  `reducedIp` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '扣量后的ip统计',
  PRIMARY KEY (`pageId`, `date`) USING BTREE,
  INDEX `pageId`(`pageId`) USING BTREE,
  INDEX `date`(`date`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '页面统计表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_page_statistic
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
        $this->execute('DROP TABLE IF EXISTS `nav_page_statistic`');
    }
}
