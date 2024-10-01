<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProstituteClick extends AbstractMigration
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
-- Table structure for p_prostitute_click
-- ----------------------------
DROP TABLE IF EXISTS `p_prostitute_click`;
CREATE TABLE `p_prostitute_click`  (
  `date` date NOT NULL DEFAULT '1000-01-01' COMMENT '日期',
  `prostituteId` int(10) UNSIGNED NOT NULL COMMENT '楼凤id',
  `contact` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '联系方式',
  `clickCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '点击计数',
  PRIMARY KEY (`date`, `prostituteId`, `contact`) USING BTREE,
  INDEX `prostituteId`(`prostituteId`) USING BTREE,
  INDEX `contact`(`contact`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '楼凤真实数据的点击记录表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `p_prostitute_click`');
    }
}
