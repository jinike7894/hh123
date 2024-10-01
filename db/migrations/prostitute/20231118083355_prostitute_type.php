<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProstituteType extends AbstractMigration
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
-- Table structure for p_prostitute_type
-- ----------------------------
DROP TABLE IF EXISTS `p_prostitute_type`;
CREATE TABLE `p_prostitute_type`  (
  `prostituteTypeId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '类型id',
  `title` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型标题',
  `typeKey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型key',
  `relatedAdId` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '关联的广告id',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，默认0，倒叙排',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`prostituteTypeId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '楼凤类型表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of p_prostitute_type
-- ----------------------------
INSERT INTO `p_prostitute_type` VALUES (1, '楼凤信息', 'Information', '1', 30, 1, NOW(), NOW());
INSERT INTO `p_prostitute_type` VALUES (2, '认证外围', 'Certified', '2', 20, 1, NOW(), NOW());
INSERT INTO `p_prostitute_type` VALUES (3, '包养入住', 'Kept', '3', 10, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `p_prostitute_type`');
    }
}
