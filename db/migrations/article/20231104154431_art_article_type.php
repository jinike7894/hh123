<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtArticleType extends AbstractMigration
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
-- Table structure for art_article_type
-- ----------------------------
DROP TABLE IF EXISTS `art_article_type`;
CREATE TABLE `art_article_type`  (
  `articleTypeId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章类型id',
  `articleTypeParentId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章类型父id',
  `articleGroupKey` enum('PornNews','Muckraking') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PornNews' COMMENT '文章分组Key\r\n1.PornNews 性闻\r\n2.Muckraking 吃瓜爆料',
  `articleTypeName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '文章类型名称',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（倒叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`articleTypeId`) USING BTREE,
  INDEX `articleTypeParentId`(`articleTypeParentId`) USING BTREE,
  INDEX `articleGroupKey`(`articleGroupKey`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章分类表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of art_article_type
-- ----------------------------
INSERT INTO `art_article_type` VALUES (1, 0, 'Muckraking', '热点事件', 10, 1, NOW(), NOW());
INSERT INTO `art_article_type` VALUES (2, 0, 'Muckraking', '往期回顾', 20, 1, NOW(), NOW());
INSERT INTO `art_article_type` VALUES (3, 0, 'Muckraking', '性癖专场', 30, 1, NOW(), NOW());
INSERT INTO `art_article_type` VALUES (4, 0, 'Muckraking', '国产探花', 40, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `art_article_type`');
    }
}
