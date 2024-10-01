<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ShortVod extends AbstractMigration
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
-- Table structure for mac_short_vod
-- ----------------------------
DROP TABLE IF EXISTS `mac_short_vod`;
CREATE TABLE `mac_short_vod`  (
  `vodId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vodName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频名',
  `vodPic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `vodPlayUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '播放地址',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `likeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '假的用来显示的点赞数',
  `realLikeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '真实点赞数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（倒叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`vodId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '短视频表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_short_vod
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
        $this->execute('DROP TABLE IF EXISTS `mac_short_vod`');
    }
}
