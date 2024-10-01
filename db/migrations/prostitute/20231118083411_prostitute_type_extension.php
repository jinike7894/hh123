<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProstituteTypeExtension extends AbstractMigration
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
-- Table structure for p_prostitute_type_extension
-- ----------------------------
DROP TABLE IF EXISTS `p_prostitute_type_extension`;
CREATE TABLE `p_prostitute_type_extension`  (
  `prostituteTypeExtId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '扩展id',
  `prostituteTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '类型id',
  `fieldKey` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '字段key',
  `fieldName` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '字段名',
  PRIMARY KEY (`prostituteTypeExtId`) USING BTREE,
  INDEX `prostituteTypeId`(`prostituteTypeId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 19 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '楼凤类型扩展字段表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of p_prostitute_type_extension
-- ----------------------------
INSERT INTO `p_prostitute_type_extension` VALUES (1, 1, 'number', '妹子数量');
INSERT INTO `p_prostitute_type_extension` VALUES (2, 1, 'age', '妹子年龄');
INSERT INTO `p_prostitute_type_extension` VALUES (3, 1, 'service', '服务项目');
INSERT INTO `p_prostitute_type_extension` VALUES (4, 1, 'duration', '服务时间');
INSERT INTO `p_prostitute_type_extension` VALUES (5, 1, 'cost', '服务费用');
INSERT INTO `p_prostitute_type_extension` VALUES (6, 1, 'avatar', '头像');
INSERT INTO `p_prostitute_type_extension` VALUES (7, 1, 'fileType', '文件类型');
INSERT INTO `p_prostitute_type_extension` VALUES (8, 1, 'author', '作者');
INSERT INTO `p_prostitute_type_extension` VALUES (9, 2, 'costP', '一次费用');
INSERT INTO `p_prostitute_type_extension` VALUES (10, 2, 'cost2P', '两次费用');
INSERT INTO `p_prostitute_type_extension` VALUES (11, 2, 'costN', '包夜费用');
INSERT INTO `p_prostitute_type_extension` VALUES (12, 2, 'service', '服务项目');
INSERT INTO `p_prostitute_type_extension` VALUES (13, 2, 'age', '年龄');
INSERT INTO `p_prostitute_type_extension` VALUES (14, 2, 'height', '身高');
INSERT INTO `p_prostitute_type_extension` VALUES (15, 3, 'gender', '性别');
INSERT INTO `p_prostitute_type_extension` VALUES (16, 3, 'cost', '费用');
INSERT INTO `p_prostitute_type_extension` VALUES (17, 3, 'age', '年龄');
INSERT INTO `p_prostitute_type_extension` VALUES (18, 3, 'height', '身高');

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `p_prostitute_type_extension`');
    }
}
