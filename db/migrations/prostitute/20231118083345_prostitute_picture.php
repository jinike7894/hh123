<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProstitutePicture extends AbstractMigration
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
-- Table structure for p_prostitute_picture
-- ----------------------------
DROP TABLE IF EXISTS `p_prostitute_picture`;
CREATE TABLE `p_prostitute_picture`  (
  `prostitutePicId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '楼凤图片id',
  `prostituteId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '楼凤id',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '链接',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序值，默认0，倒叙排',
  PRIMARY KEY (`prostitutePicId`) USING BTREE,
  INDEX `prostituteId`(`prostituteId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '楼凤图片表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `p_prostitute_picture`');
    }
}
