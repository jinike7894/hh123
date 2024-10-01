<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Live extends AbstractMigration
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
-- Table structure for live
-- ----------------------------
DROP TABLE IF EXISTS `live`;
CREATE TABLE `live`  (
  `liveId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '直播id',
  `liveTitle` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '直播标题',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `liveCover` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '直播封面',
  `liveViewers` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '直播人数',
  `liveUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '直播链接',
  `streamerNickname` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '主播昵称',
  `streamerAvatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '主播头像',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，默认0，倒叙排',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`liveId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '直播列表表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `live`');
    }
}
