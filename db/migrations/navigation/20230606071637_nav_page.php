<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavPage extends AbstractMigration
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
-- Table structure for nav_page
-- ----------------------------
DROP TABLE IF EXISTS `nav_page`;
CREATE TABLE `nav_page`  (
  `pageId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '页面名',
  `pageTemplateId` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '模板id',
  `code` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '统计代码',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `statisticEnabled` tinyint(4) NOT NULL DEFAULT 0 COMMENT '统计代码控制 1.开 0.关',
  `statisticConfig` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '统计代码控制配置',
  `ipCost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT 'ip单价',
  `latestTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '最后生成时间',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`pageId`) USING BTREE,
  UNIQUE INDEX `pageName`(`pageName`) USING BTREE,
  INDEX `pageTemplateId`(`pageTemplateId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '页面表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_page
-- ----------------------------
INSERT INTO `nav_page` VALUES (1, 'index.html', 1, '<script>console.log(\'index\')</script>', '默认页面请勿删除。', 0, '', 0.0000, '1000-01-01 00:00:00', 1, NOW(), NOW());
INSERT INTO `nav_page` VALUES (2, 'test.html', 1, '<script>console.log(\'test\')</script>', '测试页面', 0, '', 0.0000, '1000-01-01 00:00:00', 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_page`');
    }
}
