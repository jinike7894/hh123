<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SendCode extends AbstractMigration
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
-- Table structure for send_code
-- ----------------------------
DROP TABLE IF EXISTS `send_code`;
CREATE TABLE `send_code`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` enum('MS','Email') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'MS' COMMENT '消息类型\r\nMS 短信验证码\r\nEmail 表示邮箱验证码',
  `channel` enum('Default','JSMS') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Default' COMMENT '短信通道\r\n极光短信(JSMS)',
  `requestIp` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '请求ip，系统发送时则没有',
  `requestIpLong` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '请求ipLong，系统发送时则没有',
  `target` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '接收目标',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '消息内容',
  `response` varchar(1000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '响应',
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '状态 0失败 1成功',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `target`(`target`) USING BTREE,
  INDEX `requestIpLong`(`requestIpLong`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '信息发送记录' ROW_FORMAT = DYNAMIC;

-- --------
SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `user_group`');
    }
}
