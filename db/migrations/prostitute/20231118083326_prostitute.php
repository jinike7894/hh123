<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Prostitute extends AbstractMigration
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
-- Table structure for p_prostitute
-- ----------------------------
DROP TABLE IF EXISTS `p_prostitute`;
CREATE TABLE `p_prostitute`  (
  `prostituteId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '楼凤id',
  `prostituteTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型id',
  `title` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `content` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容',
  `type` enum('Real','Ad') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Ad' COMMENT '数据类型 Real 真实的，Ad 广告',
  `address` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '地址',
  `contact` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '联系方式',
  `provinceId` tinyint(3) UNSIGNED NOT NULL DEFAULT 0 COMMENT '省级id',
  `cityId` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '市级id',
  `extension` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '扩展参数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，默认0，倒叙排',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`prostituteId`) USING BTREE,
  INDEX `prostituteTypeId`(`prostituteTypeId`) USING BTREE,
  INDEX `provinceId`(`provinceId`) USING BTREE,
  INDEX `cityId`(`cityId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '楼凤表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `p_prostitute`');
    }
}
