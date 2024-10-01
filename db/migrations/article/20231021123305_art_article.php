<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ArtArticle extends AbstractMigration
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
-- Table structure for art_article
-- ----------------------------
DROP TABLE IF EXISTS `art_article`;
CREATE TABLE `art_article`  (
  `articleId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文章id',
  `articleTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类id',
  `articleGroupKey` enum('PornNews','Muckraking') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PornNews' COMMENT '文章分组Key\r\n1.PornNews 性闻\r\n2.Muckraking 吃瓜爆料',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '标题',
  `summary` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '摘要',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `cover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面图0',
  `cover1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面图1',
  `cover2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面图2',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  `readCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '假的用来显示的阅读数',
  `realReadCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '真实阅读数',
  `likeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '假的用来显示的点赞数',
  `realLikeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '真实点赞数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（倒叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`articleId`) USING BTREE,
  INDEX `title`(`title`) USING BTREE,
  INDEX `readCount`(`readCount`) USING BTREE,
  INDEX `likeCount`(`likeCount`) USING BTREE,
  INDEX `sort`(`sort`) USING BTREE,
  INDEX `realReadCount`(`realReadCount`) USING BTREE,
  INDEX `realLikeCount`(`realLikeCount`) USING BTREE,
  INDEX `articleTypeId`(`articleTypeId`) USING BTREE,
  INDEX `articleGroupKey`(`articleGroupKey`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of art_article
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
        $this->execute('DROP TABLE IF EXISTS `art_article`');
    }
}
