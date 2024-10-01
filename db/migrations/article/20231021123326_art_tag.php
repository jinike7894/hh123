<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtTag extends AbstractMigration
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
-- Table structure for art_tag
-- ----------------------------
DROP TABLE IF EXISTS `art_tag`;
CREATE TABLE `art_tag`  (
  `tagId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '标签id',
  `tagName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标签名',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（倒叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`tagId`) USING BTREE,
  INDEX `tagName`(`tagName`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '标签表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of art_tag
-- ----------------------------
INSERT INTO `art_tag` VALUES (1, '反差婊', 0, 1, NOW(), NOW());
INSERT INTO `art_tag` VALUES (2, '18禁情报', 0, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `art_tag`');
    }
}
