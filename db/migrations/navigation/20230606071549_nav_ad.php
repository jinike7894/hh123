<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NavAd extends AbstractMigration
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
-- Table structure for nav_ad
-- ----------------------------
DROP TABLE IF EXISTS `nav_ad`;
CREATE TABLE `nav_ad`  (
  `adId` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `adTypeId` smallint(5) UNSIGNED NOT NULL DEFAULT 1 COMMENT '广告分类id',
  `adName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '广告名',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `imageUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '图片地址',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '跳转链接',
  `extension` varchar(3000) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '扩展参数',
  `merchantId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '商户id',
  `cost` decimal(10, 4) UNSIGNED NOT NULL DEFAULT 0.0000 COMMENT '单次点击价格',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1.启用 0.禁用 -1.删除',
  `isTest` tinyint(4) NOT NULL DEFAULT 0 COMMENT '是否为测试数据',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`adId`) USING BTREE,
  INDEX `adName`(`adName`) USING BTREE,
  INDEX `merchantId`(`merchantId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 79 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '广告表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of nav_ad
-- ----------------------------
INSERT INTO `nav_ad` VALUES (1, 2, '顶部浮漂', 'up', '/Init/Zone/1/1.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (2, 2, '上门做爱', 'up', '/Init/Zone/2/2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (3, 2, '开元棋牌', 'up', '/Init/Zone/2/3.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (4, 4, '免费约炮', 'up', '/Init/Zone/3/3_1.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (5, 4, '上门做爱', 'up', '/Init/Zone/3/3_2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (6, 3, '珊瑚直播', 'up', '/Init/Zone/3/3_3.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (7, 3, '蜜恋直播', 'up', '/Init/Zone/3/3_4.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (8, 3, '情欲直播', 'up', '/Init/Zone/3/3_5.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (9, 2, '成人抖音', 'up', '/Init/Zone/3/3_6.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (10, 2, '免费P站', 'up', '/Init/Zone/3/3_7.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (11, 3, '妹团直播', 'up', '/Init/Zone/3/3_8.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (12, 2, '西门视频', 'up', '/Init/Zone/3/4_1.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (13, 2, '快色视频', 'up', '/Init/Zone/3/4_2.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (14, 2, '樱桃视频', 'up', '/Init/Zone/3/4_3.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (15, 2, 'pilipili', 'up', '/Init/Zone/3/4_4.gif', 'https://www.bilibili.com', '{\"35\":{\"description\":\"动漫涩情应有尽有\",\"downloads\":\"下载量：500万\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (16, 2, 'AV破解版', 'up', '/Init/Zone/3/4_5.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (17, 2, '抖阴漫画', 'up', '/Init/Zone/3/4_6.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (18, 2, '免费AV动漫', 'up', '/Init/Zone/3/4_7.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (19, 3, '伊人直播', 'up', '/Init/Zone/3/5_1.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (20, 3, '青草直播', 'up', '/Init/Zone/3/5_2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (21, 5, '多米体育', 'up', '/Init/Zone/3/6_1.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (22, 5, '开元棋牌', 'up', '/Init/Zone/3/6_2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (23, 5, '澳门新葡京', 'up', '/Init/Zone/3/6_3.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (24, 5, '官方威尼斯人', 'up', '/Init/Zone/3/6_4.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (25, 5, '太阳城集团', 'up', '/Init/Zone/3/6_5.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (26, 5, '永利娱乐城', 'up', '/Init/Zone/3/6_6.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (27, 4, '免费约炮', 'up', '/Init/Zone/3/3_1.gif', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"热门\",\"times\":\"4257004\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (28, 4, '上门做爱', 'up', '/Init/Zone/4/7_1.gif', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"热门\",\"times\":\"831204\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (29, 3, '蜜恋直播', 'up', '/Init/Zone/3/3_4.gif', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"热门\",\"times\":\"2196780\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (30, 2, '免费P站', 'up', '/Init/Zone/3/3_7.gif', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"推荐\",\"times\":\"1834574\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (31, 2, '免费X站', 'up', '/Init/Zone/4/7_2.png', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"推荐\",\"times\":\"8751654\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (32, 2, '91免费版', 'up', '/Init/Zone/4/7_3.gif', 'https://www.bilibili.com', '{\"7\":{\"tag\":\"推荐\",\"times\":\"722524\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (33, 2, '约会1', 'up', '/Init/Zone/5/1.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"在校学生\",\"tag2\":\"寻求刺激\",\"tag3\":\"潮吹喷水\",\"age\":\"19\",\"height\":\"163cm\",\"cup\":\"B杯罩\",\"district\":\"上海\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (34, 2, '约会2', 'up', '/Init/Zone/5/2.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"清纯妹妹\",\"tag2\":\"可盐可甜\",\"tag3\":\"乖巧可爱\",\"age\":\"24\",\"height\":\"168cm\",\"cup\":\"C杯罩\",\"district\":\"成都\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (35, 2, '约会3', 'up', '/Init/Zone/5/3.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"公司秘书\",\"tag2\":\"野性放荡\",\"tag3\":\"反差美女\",\"age\":\"24\",\"height\":\"172cm\",\"cup\":\"D杯罩\",\"district\":\"北京\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (36, 2, '约会4', 'up', '/Init/Zone/5/4.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"超级辣妈\",\"tag2\":\"老公不在\",\"tag3\":\"家里可约\",\"age\":\"25\",\"height\":\"163cm\",\"cup\":\"C杯罩\",\"district\":\"深圳\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (37, 2, '约会5', 'up', '/Init/Zone/5/5.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"极品御姐\",\"tag2\":\"粉嫩无毛\",\"tag3\":\"酒店可约\",\"age\":\"27\",\"height\":\"174cm\",\"cup\":\"F杯罩\",\"district\":\"全国\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (38, 2, '约会6', 'up', '/Init/Zone/5/6.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"兼职约啪\",\"tag2\":\"在校学生\",\"tag3\":\"交友线下\",\"age\":\"19\",\"height\":\"167cm\",\"cup\":\"B杯罩\",\"district\":\"成都\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (39, 2, '约会7', 'up', '/Init/Zone/5/7.png', 'https://www.bilibili.com', '{\"8\":{\"tag1\":\"姐妹花❀\",\"tag2\":\"性感双飞\",\"tag3\":\"随时可约\",\"age\":\"23\",\"height\":\"170cm\",\"cup\":\"C杯罩\",\"district\":\"全国\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (40, 2, '底部浮漂', 'up', '/Init/Zone/6/1.gif', 'https://www.bilibili.com', '{\"9\":{\"title\":\"上门做爱\",\"description\":\"打造最高端的情色盛宴\"},\"36\":{\"title\":\"上门做爱\",\"description\":\"打造最高端的情色盛宴\"},\"37\":{\"title\":\"上门做爱\",\"description\":\"打造最高端的情色盛宴\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (41, 2, '51顶部logo', 'up', '/Init/Zone/7/1.png', 'https://www.bilibili.com', '{\"11\":{\"announcement\":\"公告：最新地址 https://abc.efg.com 本站所发布APP均已测试，请放心下载！由于带有成人内容，手机报毒属正常现象！狼友可多下载几款防丢失！商务TG：https://t.me/xxxxxxx\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (42, 2, '51顶部横幅', 'up', '/Init/Zone/8/1.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (43, 4, '同城约炮', 'up', '/Init/Zone/9/1.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (44, 3, '密爱直播', 'up', '/Init/Zone/9/2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (45, 5, '开元棋牌', 'up', '/Init/Zone/9/3.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (46, 2, '她趣', 'up', '/Init/Zone/9/4.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (47, 3, '白小姐直播', 'up', '/Init/Zone/9/5.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (48, 2, '海角乱伦社区', 'up', '/Init/Zone/9/6.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (49, 2, '91全能版', 'up', '/Init/Zone/9/7.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (50, 2, '微密圈破解版', 'up', '/Init/Zone/9/8.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (51, 3, '妖爱直播', 'up', '/Init/Zone/9/9.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (52, 2, '私房KTV', 'up', '/Init/Zone/9/10.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (53, 2, '暗网禁区', 'up', '/Init/Zone/9/11.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (54, 2, '黑洞', 'up', '/Init/Zone/9/12.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (55, 5, '开元棋牌横幅', 'up', '/Init/Zone/10/1.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (56, 2, '密爱直播横幅', 'up', '/Init/Zone/10/2.gif', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (57, 2, '91横幅', 'up', '/Init/Zone/10/3.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (58, 5, '开元棋牌下载', 'up', '/Init/Zone/9/3.png', 'https://www.bilibili.com', '{\"15\":{\"title\":\"开元棋牌\",\"description\":\"注册用888元 捕鱼爆大奖 天天领红包 官网直营 6046.com\",\"downloads\":\"483\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (59, 3, '密爱直播下载', 'up', '/Init/Zone/9/2.gif', 'https://www.bilibili.com', '{\"15\":{\"title\":\"密爱直播\",\"description\":\"学妹深夜直播自慰 御姐表演刺激潮喷 数万老师在线性爱教学\",\"downloads\":\"558\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (60, 4, '同城约炮下载', 'up', '/Init/Zone/11/3.gif', 'https://www.bilibili.com', '{\"15\":{\"title\":\"同城约炮 首单免费\",\"description\":\"嫩模空降 学生上门 莞式服务 任意调教 帝王享受 制服调教\",\"downloads\":\"451\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (61, 2, '51吃瓜下载', 'up', '/Init/Zone/11/4.gif', 'https://www.bilibili.com', '{\"15\":{\"title\":\"51吃瓜\",\"description\":\"51吃瓜带你吃最新最热的瓜！ 微密圈最新福利时时更新\",\"downloads\":\"966\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (62, 2, '她趣下载', 'up', '/Init/Zone/9/4.png', 'https://www.bilibili.com', '{\"15\":{\"title\":\"她趣\",\"description\":\"国内首家乱伦开放社区\",\"downloads\":\"492\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (63, 2, '微密圈破解版下载', 'up', '/Init/Zone/9/8.png', 'https://www.bilibili.com', '{\"15\":{\"title\":\"微密圈破解版\",\"description\":\"免费微密圈，白嫖20万圈主的私密内容分享，张老师，小王同学，阿朱，牛奶秋刀姨，小厨娘美食记，铁锤姐姐，尤妮丝....\",\"downloads\":\"2165\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (64, 2, '暗网禁区下载', 'up', '/Init/Zone/9/11.png', 'https://www.bilibili.com', '{\"15\":{\"title\":\"暗网禁区\",\"description\":\"最大的暗网自由开放圈\",\"downloads\":\"4973\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (65, 2, '51动漫下载', 'up', '/Init/Zone/11/8.png', 'https://www.bilibili.com', '{\"15\":{\"title\":\"51动漫\",\"description\":\"成人动漫，最全涩漫高能来袭，每日更新看不停\",\"downloads\":\"414\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (66, 4, '同城约炮底部浮漂', 'up', '/Init/Zone/9/1.gif', 'https://www.bilibili.com', '{\"16\":{\"title\":\"免费约炮\",\"description\":\"打造最满意的约炮性福体验，同城邀约~\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (67, 2, '抹茶logo', 'up', '/Init/Zone/13/logo.png', 'https://www.bilibili.com', '{\"21\":{\"title\":\"永久域名:8mz.cc\",\"announcement\":\"因为APP中含有成人内容，所以可能会出现“恶意软件”或“病毒软件”的提示。\",\"link\":\"https:\\/\\/8mz.cc\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (68, 2, '抹茶分享图', 'up', '/Init/Zone/19/bg.png', 'https://www.bilibili.com', '{\"27\":{\"link\":\"https:\\/\\/www.bilibili.com\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (69, 2, '抹茶楼凤1', 'up', '/Init/Zone/22/d1.png', 'https://www.bilibili.com', '{\"30\":{\"text1\":\"在校学生\",\"text2\":\"人约过\",\"range\":\"50,100\",\"name\":\"小美的美丽\",\"age\":\"23\",\"project\":\"气质型御姐，清纯甜美在线兼职\",\"mode\":\"到店 上门\",\"introduction\":\"新人01年 身高170\\n本人保真，身材完美，女神荣耀\\n温柔乖巧听话懂事\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (70, 2, '抹茶楼凤2', 'up', '/Init/Zone/22/d2.png', 'https://www.bilibili.com', '{\"30\":{\"text1\":\"人妻\",\"text2\":\"人约过\",\"range\":\"40,80\",\"name\":\"来日方长\",\"age\":\"23\",\"project\":\"气质型御姐，清纯甜美在线兼职\",\"mode\":\"到店 上门\",\"introduction\":\"新人02年 身高172\\n本人保真，身材完美，女神荣耀\\n温柔乖巧听话懂事\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (71, 2, '抹茶楼凤3', 'up', '/Init/Zone/22/d3.png', 'https://www.bilibili.com', '{\"30\":{\"text1\":\"护士\",\"text2\":\"人约过\",\"range\":\"75,125\",\"name\":\"小丽\",\"age\":\"23\",\"project\":\"气质型御姐，清纯甜美在线兼职\",\"mode\":\"到店 上门\",\"introduction\":\"新人02年 身高175\\n本人保真，可各种制服\\n温柔乖巧听话懂事\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (72, 2, '抹茶楼凤4', 'up', '/Init/Zone/22/d4.png', 'https://www.bilibili.com', '{\"30\":{\"text1\":\"少妇\",\"text2\":\"人约过\",\"range\":\"30,80\",\"name\":\"点点雨露\",\"age\":\"23\",\"project\":\"气质型御姐，清纯甜美在线兼职\",\"mode\":\"到店 上门\",\"introduction\":\"新人03年 身高165\\n本人保真，身材完美，小家碧玉\\n温柔乖巧听话懂事\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (73, 2, '抹茶直播1', 'up', '/Init/Zone/22/l1.png', 'https://www.bilibili.com', '{\"31\":{\"tag\":\"热门\",\"title\":\"1对1直播\",\"text1\":\"人在线\",\"range\":\"1234,4321\",\"name\":\"小美\",\"introduction\":\"可爱的小姐姐\\n懂事温柔 听话\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (74, 2, '抹茶直播2', 'up', '/Init/Zone/22/l2.png', 'https://www.bilibili.com', '{\"31\":{\"tag\":\"热门\",\"title\":\"在线喷水\",\"text1\":\"人在线\",\"range\":\"1000,2000\",\"name\":\"性感阿妹\",\"introduction\":\"可爱的小姐姐\\n欢迎来撩\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (75, 2, '抹茶直播3', 'up', '/Init/Zone/22/l3.png', 'https://www.bilibili.com', '{\"31\":{\"tag\":\"热门\",\"title\":\"在线调教\",\"text1\":\"人在线\",\"range\":\"888,1888\",\"name\":\"xiao美M\",\"introduction\":\"可爱的小姐姐\\n懂事温柔 听话\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (76, 2, '抹茶直播4', 'up', '/Init/Zone/22/l4.png', 'https://www.bilibili.com', '{\"31\":{\"tag\":\"热门\",\"title\":\"直播操逼\",\"text1\":\"人在线\",\"range\":\"2111,3000\",\"name\":\"秀色可餐\",\"introduction\":\"直播大秀\\n多人激情\"}}', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (77, 1, '抹茶横幅小1', 'up', '/Init/Zone/14/1.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());
INSERT INTO `nav_ad` VALUES (78, 1, '抹茶横幅小2', 'up', '/Init/Zone/14/2.png', 'https://www.bilibili.com', '[]', 1, 0.0000, '', 1, 1, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;

sql;

        $count = $this->execute($sql);
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->execute('DROP TABLE IF EXISTS `nav_ad`');
    }
}
