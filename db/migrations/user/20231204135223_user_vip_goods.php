<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserVipGoods extends AbstractMigration
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
-- Table structure for user_vip_goods
-- ----------------------------
DROP TABLE IF EXISTS `user_vip_goods`;
CREATE TABLE `user_vip_goods`  (
  `goodsId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '商品id',
  `goodsKey` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品key',
  `goodsType` enum('Common','NewUser') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Common' COMMENT '商品类型',
  `goodsName` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品名',
  `goodsIntroduction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '商品介绍',
  `goodsOriginalPrice` decimal(8, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品原价',
  `goodsPresentPrice` decimal(8, 2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT '商品现价',
  `days` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '生效天数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（正叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`goodsId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '用户开会员商品表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user_vip_goods
-- ----------------------------
INSERT INTO `user_vip_goods` VALUES (1, 'Month', 'Common', '月卡', '30天看VIP视频', 499.00, 50.00, 30, 10, 1, NOW(), NOW());
INSERT INTO `user_vip_goods` VALUES (2, 'Quarter', 'Common', '季卡', '90天看VIP视频', 999.00, 100.00, 90, 20, 1, NOW(), NOW());
INSERT INTO `user_vip_goods` VALUES (3, 'Year', 'Common', '年卡', '365天看VIP视频', 1999.00, 200.00, 365, 30, 1, NOW(), NOW());
INSERT INTO `user_vip_goods` VALUES (4, 'Forever', 'Common', '永久卡', '永久看VIP视频', 2999.00, 300.00, 36500, 40, 1, NOW(), NOW());
INSERT INTO `user_vip_goods` VALUES (5, 'Forever', 'NewUser', '新人限时永久卡', '新人限时永久卡', 300.00, 100.00, 36500, 0, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `user_vip_goods`');
    }
}
