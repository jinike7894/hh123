<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavPageTemplate extends AbstractMigration
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
-- Table structure for nav_page_template
-- ----------------------------
DROP TABLE IF EXISTS `nav_page_template`;
CREATE TABLE `nav_page_template`  (
  `pageTemplateId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pageTemplateName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '模板名',
  `pageTemplateKey` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '模板键',
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '描述',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`pageTemplateId`) USING BTREE,
  INDEX `pageTemplateKey`(`pageTemplateKey`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '模板表，当新增模板的时候需要在这添加一条数据。' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_page_template
-- ----------------------------
INSERT INTO `nav_page_template` VALUES (1, '默认', 'default', '默认样式', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (2, '默认_测试', 'defaultTest', 'tab的游戏换成赚钱', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (3, '模板1', 'template1', 'banner和tab换位置', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (4, '51导航', '51Navigation', '51导航的模板样式', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (5, '51导航1', '51Navigation1', '51导航2个列表', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (6, '51导航2', '51Navigation2', '51导航列表换tab', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (7, '抹茶', 'matcha', '抹茶影视', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (8, '51导航3', '51Navigation3', '和51导航2一样', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (9, '3图单页', '3pictures', '3图单页', NOW(), NOW());
INSERT INTO `nav_page_template` VALUES (10, '性浪', 'xingLang', '性浪', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_page_template`');
    }
}
