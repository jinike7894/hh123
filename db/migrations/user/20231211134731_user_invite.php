<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserInvite extends AbstractMigration
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
-- Table structure for user_invite
-- ----------------------------
DROP TABLE IF EXISTS `user_invite`;
CREATE TABLE `user_invite`  (
  `userInviteId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `inviterId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '邀请人用户id',
  `inviteeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '被邀请人用户id',
  `createDate` date NOT NULL DEFAULT '1000-01-01' COMMENT '创建日期',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`userInviteId`) USING BTREE,
  INDEX `inviterId`(`inviterId`) USING BTREE,
  INDEX `inviteeId`(`inviteeId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户邀请记录表' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `user_invite`');
    }
}
