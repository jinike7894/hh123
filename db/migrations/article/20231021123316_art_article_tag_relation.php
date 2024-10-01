<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtArticleTagRelation extends AbstractMigration
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
-- Table structure for art_article_tag_relation
-- ----------------------------
DROP TABLE IF EXISTS `art_article_tag_relation`;
CREATE TABLE `art_article_tag_relation`  (
  `articleId` int(10) UNSIGNED NOT NULL COMMENT '文章id',
  `tagId` int(10) UNSIGNED NOT NULL COMMENT '标签id',
  PRIMARY KEY (`articleId`, `tagId`) USING BTREE,
  INDEX `tagId`(`tagId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章与标签关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of art_article_tag_relation
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
        $this->execute('DROP TABLE IF EXISTS `art_article_tag_relation`');
    }
}
