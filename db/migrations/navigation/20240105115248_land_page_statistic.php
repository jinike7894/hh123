<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LandPageStatistic extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function up()
    {
        $sql = <<<'sql'

CREATE TABLE `nav_landpage_statistic`  (
   `channelKey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '渠道key',
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

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_landpage_statistic`');
    }
}
