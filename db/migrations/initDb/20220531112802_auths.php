<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Auths extends AbstractMigration
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
-- Table structure for auths
-- ----------------------------
DROP TABLE IF EXISTS `auths`;
CREATE TABLE `auths`  (
  `authId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parentAuthId` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父级权限id',
  `authName` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '权限名',
  `authRule` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '路由地址',
  `authController` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '路由控制器',
  `authAction` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '路由方法',
  `authType` tinyint(1) NOT NULL DEFAULT 1 COMMENT '权限类型 0菜单1按钮',
  `isLog` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否记录日志(0 不记录 1记录)',
  `authIcon` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '路由图标',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`authId`) USING BTREE,
  INDEX `parentAuthId`(`parentAuthId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 76 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of auths
-- ----------------------------
INSERT INTO `auths` VALUES (1, 0, '站点管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (2, 1, '站点配置列表', '/Api/Admin/System/Website/configList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (3, 1, '站点配置设置', '/Api/Admin/System/Website/setConfig', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (4, 0, '权限管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (5, 4, '角色列表', '/Api/Admin/AuthsManage/Roles', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (6, 5, '角色添加', '/Api/Admin/AuthsManage/Roles/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (7, 5, '角色编辑', '/Api/Admin/AuthsManage/Roles/update', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (8, 4, '管理员列表', '/Api/Admin/Account/adminList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (9, 8, '管理员添加', '/Api/Admin/Account/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (10, 8, '编辑管理员', '/Api/Admin/Account/update', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (11, 8, '管理员详情', '/Api/Admin/Account/getOne', '', '', 1, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (12, 8, '管理员日志', '/Api/Admin/AuthsManage/AdminLogs', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (13, 8, '绑定google验证器', '/Api/Admin/Account/bindGoogleAuthenticator', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (14, 8, '验证绑定google验证器', '/Api/Admin/Account/checkBindGoogleAuthenticator', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (15, 8, '更新个人资料（公共）', '/Api/Admin/Account/updatePersonal', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (16, 1, '导航配置列表', '/Api/Admin/System/Navigation/configList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (17, 1, '导航配置设置', '/Api/Admin/System/Navigation/setConfig', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (18, 0, '广告管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (19, 18, '广告列表', '/Api/Admin/Navigation/Ad/adList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (20, 18, '广告详情', '/Api/Admin/Navigation/Ad/adDetail', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (21, 18, '广告添加', '/Api/Admin/Navigation/Ad/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (22, 18, '广告修改', '/Api/Admin/Navigation/Ad/edit', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (23, 18, '广告状态修改', '/Api/Admin/Navigation/Ad/setStatus', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (24, 18, '广告删除', '/Api/Admin/Navigation/Ad/delete', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (25, 18, '广告点击统计列表', '/Api/Admin/Navigation/AdClickStatistic/getList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (26, 18, '广告组列表', '/Api/Admin/Navigation/AdGroup/groupList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (27, 0, '页面管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (28, 27, '页面列表', '/Api/Admin/Navigation/Page/pageList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (29, 27, '全页面关联列表', '/Api/Admin/Navigation/Page/pageListAll', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (30, 27, '页面详情', '/Api/Admin/Navigation/Page/pageDetail', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (31, 27, '页面添加', '/Api/Admin/Navigation/Page/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (32, 27, '页面修改', '/Api/Admin/Navigation/Page/edit', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (33, 27, '页面删除', '/Api/Admin/Navigation/Page/delete', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (34, 27, '页面生成', '/Api/Admin/Navigation/Page/create', '', '', 1, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (35, 0, '模板管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (36, 35, '页面模板列表', '/Api/Admin/Navigation/PageTemplate/templateList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (37, 35, '页面模板详情', '/Api/Admin/Navigation/PageTemplate/templateDetail', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (38, 35, '模板状态修改', '/Api/Admin/Navigation/PageTemplate/setStatus', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (39, 35, '模板排序修改', '/Api/Admin/Navigation/PageTemplate/setSort', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (40, 35, '模板数据缓存删除', '/Api/Admin/Navigation/PageTemplate/deleteCache', '', '', 1, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (41, 0, '商户管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (42, 41, '商户列表', '/Api/Admin/Merchant/Merchant/merchantList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (43, 41, '全商户关联列表', '/Api/Admin/Merchant/Merchant/merchantListAll', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (44, 41, '商户详情', '/Api/Admin/Merchant/Merchant/merchantDetail', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (45, 41, '商户添加', '/Api/Admin/Merchant/Merchant/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (46, 41, '商户编辑', '/Api/Admin/Merchant/Merchant/edit', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (47, 41, '删除商户', '/Api/Admin/Merchant/Merchant/delete', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (48, 41, '商户账变列表', '/Api/Admin/Merchant/Merchant/balanceChangeList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (49, 41, '手动调整余额', '/Api/Admin/Merchant/Merchant/manualChangeBalance', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (50, 41, '商户余额提醒', '/Api/Admin/Merchant/Merchant/balanceReminder', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (51, 41, '商户仪表盘（商户）', '/Api/Admin/Merchant/Merchant/dashboard', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (52, 0, '公共接口', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (53, 52, '账变类型列表', '/Api/Admin/Common/BalanceChangeType/getList', '', '', 1, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (54, 52, '上传图片', '/Api/Admin/Upload/image', '', '', 1, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (55, 1, '获取app配置列表', '/Api/Admin/System/App/configList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (56, 1, '设置导航配置', '/Api/Admin/System/App/setConfig', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (57, 0, '渠道管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (58, 57, '渠道列表', '/Api/Admin/Merchant/Channel/channelList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (59, 57, '渠道添加', '/Api/Admin/Merchant/Channel/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (60, 57, '渠道编辑', '/Api/Admin/Merchant/Channel/edit', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (61, 57, '修改渠道状态', '/Api/Admin/Merchant/Channel/setStatus', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (62, 57, '渠道删除', '/Api/Admin/Merchant/Channel/delete', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (63, 57, '渠道统计列表', '/Api/Admin/Merchant/Channel/statisticListSystem', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (64, 57, '我的渠道列表（商户）', '/Api/Admin/Merchant/Channel/myChannelList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (65, 57, '渠道统计列表（商户）', '/Api/Admin/Merchant/Channel/statisticList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (66, 18, '广告点击记录列表', '/Api/Admin/Navigation/AdClickRecord/getList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (67, 18, '广告点击总计统计列表', '/Api/Admin/Navigation/AdClickStatistic/getTotalList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (68, 0, '广告分类管理', '', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (69, 68, '获取广告分类列表', '/Api/Admin/Navigation/AdType/typeList', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (70, 68, '添加广告分类', '/Api/Admin/Navigation/AdType/add', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (71, 68, '修改广告分类', '/Api/Admin/Navigation/AdType/edit', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (72, 68, '修改广告分类状态', '/Api/Admin/Navigation/AdType/setStatus', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (73, 68, '删除广告分类', '/Api/Admin/Navigation/AdType/delete', '', '', 1, 1, '', NOW(), NOW());
INSERT INTO `auths` VALUES (74, 57, '渠道总计列表', '/Api/Admin/Merchant/Channel/statisticTotalListSystem', '', '', 0, 0, '', NOW(), NOW());
INSERT INTO `auths` VALUES (75, 57, '渠道总计图表数据', '/Api/Admin/Merchant/Channel/statisticTotalChartSystem', '', '', 0, 0, '', NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `auths`');
    }
}
