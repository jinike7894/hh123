<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AdminLog extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $sql = <<<sql
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adminId` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `type` enum('Select','Add','Update','Delete') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Add' COMMENT '操作类型(Select查，Add 添加，Update 修改，Delete 删除)',
  `logModule` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作模块',
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '方法名称',
  `authId` int(11) NOT NULL DEFAULT 0 COMMENT '关联权限表id',
  `status` smallint(4) NOT NULL DEFAULT 0 COMMENT '(0失败，1成功)',
  `content` varchar(3072) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作内容',
  `requestIp` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '操作ip',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '操作时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `userId`(`adminId`) USING BTREE,
  INDEX `logModule`(`logModule`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户操作日志' ROW_FORMAT = COMPACT;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `admin_logs`');
    }
}
