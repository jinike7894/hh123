/*
 Navicat MySQL Data Transfer

 Source Server         : 127.0.0.1 5.7
 Source Server Type    : MySQL
 Source Server Version : 50742
 Source Host           : 127.0.0.1:3307
 Source Schema         : matcha

 Target Server Type    : MySQL
 Target Server Version : 50742
 File Encoding         : 65001

 Date: 22/11/2023 14:35:24
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for mac_actor
-- ----------------------------
DROP TABLE IF EXISTS `mac_actor`;
CREATE TABLE `mac_actor`  (
  `actor_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `type_id_1` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `actor_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_alias` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `actor_lock` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `actor_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_sex` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_blurb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_area` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_height` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_weight` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_birthday` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_birtharea` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_blood` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_starsign` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_school` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_works` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_class` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `actor_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `actor_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `actor_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `actor_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `actor_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `actor_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `actor_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_jumpurl` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `actor_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`actor_id`) USING BTREE,
  INDEX `type_id`(`type_id`) USING BTREE,
  INDEX `type_id_1`(`type_id_1`) USING BTREE,
  INDEX `actor_name`(`actor_name`) USING BTREE,
  INDEX `actor_en`(`actor_en`) USING BTREE,
  INDEX `actor_letter`(`actor_letter`) USING BTREE,
  INDEX `actor_level`(`actor_level`) USING BTREE,
  INDEX `actor_time`(`actor_time`) USING BTREE,
  INDEX `actor_time_add`(`actor_time_add`) USING BTREE,
  INDEX `actor_sex`(`actor_sex`) USING BTREE,
  INDEX `actor_area`(`actor_area`) USING BTREE,
  INDEX `actor_up`(`actor_up`) USING BTREE,
  INDEX `actor_down`(`actor_down`) USING BTREE,
  INDEX `actor_tag`(`actor_tag`) USING BTREE,
  INDEX `actor_class`(`actor_class`) USING BTREE,
  INDEX `actor_score`(`actor_score`) USING BTREE,
  INDEX `actor_score_all`(`actor_score_all`) USING BTREE,
  INDEX `actor_score_num`(`actor_score_num`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_actor
-- ----------------------------

-- ----------------------------
-- Table structure for mac_admin
-- ----------------------------
DROP TABLE IF EXISTS `mac_admin`;
CREATE TABLE `mac_admin`  (
  `admin_id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `admin_pwd` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `admin_random` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `admin_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `admin_auth` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `admin_login_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_login_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_login_num` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_last_login_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `admin_last_login_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`admin_id`) USING BTREE,
  INDEX `admin_name`(`admin_name`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_admin
-- ----------------------------
INSERT INTO `mac_admin` VALUES (1, 'admin', '200820e3227815ed1756a6b531e7e0d2', '9e908611d1cf3586690b28f7f88ff09c', 1, '', 1700117614, 2886860801, 11, 1699341421, 2886860801);

-- ----------------------------
-- Table structure for mac_annex
-- ----------------------------
DROP TABLE IF EXISTS `mac_annex`;
CREATE TABLE `mac_annex`  (
  `annex_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `annex_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `annex_file` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `annex_size` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `annex_type` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`annex_id`) USING BTREE,
  INDEX `annex_time`(`annex_time`) USING BTREE,
  INDEX `annex_file`(`annex_file`) USING BTREE,
  INDEX `annex_type`(`annex_type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_annex
-- ----------------------------

-- ----------------------------
-- Table structure for mac_art
-- ----------------------------
DROP TABLE IF EXISTS `mac_art`;
CREATE TABLE `mac_art`  (
  `art_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `type_id_1` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `group_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `art_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_sub` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `art_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_from` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_author` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_tag` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_class` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pic_thumb` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pic_slide` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pic_screenshot` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `art_blurb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_jumpurl` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `art_lock` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `art_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `art_points_detail` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `art_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `art_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `art_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `art_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `art_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `art_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `art_rel_art` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_rel_vod` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pwd` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_pwd_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `art_title` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `art_note` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `art_content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`art_id`) USING BTREE,
  INDEX `type_id`(`type_id`) USING BTREE,
  INDEX `type_id_1`(`type_id_1`) USING BTREE,
  INDEX `art_level`(`art_level`) USING BTREE,
  INDEX `art_hits`(`art_hits`) USING BTREE,
  INDEX `art_time`(`art_time`) USING BTREE,
  INDEX `art_letter`(`art_letter`) USING BTREE,
  INDEX `art_down`(`art_down`) USING BTREE,
  INDEX `art_up`(`art_up`) USING BTREE,
  INDEX `art_tag`(`art_tag`) USING BTREE,
  INDEX `art_name`(`art_name`) USING BTREE,
  INDEX `art_enn`(`art_en`) USING BTREE,
  INDEX `art_hits_day`(`art_hits_day`) USING BTREE,
  INDEX `art_hits_week`(`art_hits_week`) USING BTREE,
  INDEX `art_hits_month`(`art_hits_month`) USING BTREE,
  INDEX `art_time_add`(`art_time_add`) USING BTREE,
  INDEX `art_time_make`(`art_time_make`) USING BTREE,
  INDEX `art_lock`(`art_lock`) USING BTREE,
  INDEX `art_score`(`art_score`) USING BTREE,
  INDEX `art_score_all`(`art_score_all`) USING BTREE,
  INDEX `art_score_num`(`art_score_num`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_art
-- ----------------------------

-- ----------------------------
-- Table structure for mac_card
-- ----------------------------
DROP TABLE IF EXISTS `mac_card`;
CREATE TABLE `mac_card`  (
  `card_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `card_no` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `card_pwd` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `card_money` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `card_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `card_use_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `card_sale_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `card_add_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `card_use_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`card_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `card_add_time`(`card_add_time`) USING BTREE,
  INDEX `card_use_time`(`card_use_time`) USING BTREE,
  INDEX `card_no`(`card_no`) USING BTREE,
  INDEX `card_pwd`(`card_pwd`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_card
-- ----------------------------

-- ----------------------------
-- Table structure for mac_cash
-- ----------------------------
DROP TABLE IF EXISTS `mac_cash`;
CREATE TABLE `mac_cash`  (
  `cash_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cash_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `cash_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `cash_money` decimal(12, 2) UNSIGNED NOT NULL DEFAULT 0.00,
  `cash_bank_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `cash_bank_no` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `cash_payee_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `cash_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cash_time_audit` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`cash_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `cash_status`(`cash_status`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_cash
-- ----------------------------

-- ----------------------------
-- Table structure for mac_cj_content
-- ----------------------------
DROP TABLE IF EXISTS `mac_cj_content`;
CREATE TABLE `mac_cj_content`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nodeid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `url` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `title` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `data` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `nodeid`(`nodeid`) USING BTREE,
  INDEX `status`(`status`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_cj_content
-- ----------------------------

-- ----------------------------
-- Table structure for mac_cj_history
-- ----------------------------
DROP TABLE IF EXISTS `mac_cj_history`;
CREATE TABLE `mac_cj_history`  (
  `md5` char(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`md5`) USING BTREE,
  INDEX `md5`(`md5`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of mac_cj_history
-- ----------------------------

-- ----------------------------
-- Table structure for mac_cj_node
-- ----------------------------
DROP TABLE IF EXISTS `mac_cj_node`;
CREATE TABLE `mac_cj_node`  (
  `nodeid` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `lastdate` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `sourcecharset` varchar(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sourcetype` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `urlpage` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `pagesize_start` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `pagesize_end` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `page_base` char(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `par_num` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `url_contain` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `url_except` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `url_start` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `url_end` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `title_rule` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `title_html_rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type_rule` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `type_html_rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content_rule` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content_html_rule` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content_page_start` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content_page_end` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `content_page_rule` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `content_page` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `content_nextpage` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `down_attachment` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `watermark` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `coll_order` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `customize_config` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `program_config` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `mid` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`nodeid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_cj_node
-- ----------------------------

-- ----------------------------
-- Table structure for mac_collect
-- ----------------------------
DROP TABLE IF EXISTS `mac_collect`;
CREATE TABLE `mac_collect`  (
  `collect_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `collect_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `collect_mid` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `collect_appid` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_appkey` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_param` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_filter` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `collect_filter_from` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `collect_opt` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `collect_sync_pic_opt` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '同步图片选项，0-跟随全局，1-开启，2-关闭',
  PRIMARY KEY (`collect_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_collect
-- ----------------------------
INSERT INTO `mac_collect` VALUES (1, '快看资源网', 'https://kuaikan-api.com/api.php/provide/vod/from/kuaikan/', 2, 1, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (2, '真不卡', 'http://www.bukazyw.com/api.php/provide/vod/?ac=list', 2, 1, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (3, '狼友会', 'https://api.lyhapi.com/api.php/provide/vod/?ac=list', 2, 1, '', '', '', 0, 'lyh', 0, 0);
INSERT INTO `mac_collect` VALUES (4, 'nai小说', 'https://naixxzy.com/api.php/provide/art/?ac=list', 2, 2, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (5, '速看资源', 'https://ziyuan.skm3u8.com/api.php/provide/vod', 2, 1, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (6, 'OK资源网', 'https://okzyw.top/api.php/provide/vod/', 2, 1, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (7, '辣椒资源', 'https://apilj.com/api.php/provide/vod/at/xml/', 1, 1, '', '', '', 0, '', 0, 0);
INSERT INTO `mac_collect` VALUES (8, '森林资源', 'https://slapibf.com/api.php/provide/vod/?ac=list', 2, 1, '', '', '', 0, '', 0, 0);

-- ----------------------------
-- Table structure for mac_comment
-- ----------------------------
DROP TABLE IF EXISTS `mac_comment`;
CREATE TABLE `mac_comment`  (
  `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `comment_mid` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `comment_rid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comment_pid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comment_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `comment_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `comment_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comment_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comment_content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `comment_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `comment_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `comment_reply` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `comment_report` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_id`) USING BTREE,
  INDEX `comment_mid`(`comment_mid`) USING BTREE,
  INDEX `comment_rid`(`comment_rid`) USING BTREE,
  INDEX `comment_time`(`comment_time`) USING BTREE,
  INDEX `comment_pid`(`comment_pid`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `comment_reply`(`comment_reply`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_comment
-- ----------------------------

-- ----------------------------
-- Table structure for mac_gbook
-- ----------------------------
DROP TABLE IF EXISTS `mac_gbook`;
CREATE TABLE `mac_gbook`  (
  `gbook_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `gbook_rid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gbook_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `gbook_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `gbook_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gbook_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gbook_reply_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gbook_content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `gbook_reply` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`gbook_id`) USING BTREE,
  INDEX `gbook_rid`(`gbook_rid`) USING BTREE,
  INDEX `gbook_time`(`gbook_time`) USING BTREE,
  INDEX `gbook_reply_time`(`gbook_reply_time`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `gbook_reply`(`gbook_reply`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_gbook
-- ----------------------------

-- ----------------------------
-- Table structure for mac_group
-- ----------------------------
DROP TABLE IF EXISTS `mac_group`;
CREATE TABLE `mac_group`  (
  `group_id` smallint(6) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `group_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `group_type` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `group_popedom` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `group_points_day` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `group_points_week` smallint(6) NOT NULL DEFAULT 0,
  `group_points_month` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `group_points_year` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `group_points_free` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`) USING BTREE,
  INDEX `group_status`(`group_status`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_group
-- ----------------------------
INSERT INTO `mac_group` VALUES (1, '游客', 1, ',1,6,7,8,9,10,11,12,2,13,14,15,16,3,4,5,17,18,', '{\"1\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"6\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"7\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"8\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"9\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"10\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"11\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"12\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"2\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"13\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"14\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"15\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"16\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"3\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"4\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"5\":{\"1\":\"1\",\"2\":\"2\"},\"17\":{\"1\":\"1\",\"2\":\"2\"},\"18\":{\"1\":\"1\",\"2\":\"2\"}}', 0, 0, 0, 0, 0);
INSERT INTO `mac_group` VALUES (2, '默认会员', 1, ',1,6,7,8,9,10,11,12,2,13,14,15,16,3,4,5,17,18,', '{\"1\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"6\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"7\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"8\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"9\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"10\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"11\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"12\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"2\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"13\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"14\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"15\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"16\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"3\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"4\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"5\":{\"1\":\"1\",\"2\":\"2\"},\"17\":{\"1\":\"1\",\"2\":\"2\"},\"18\":{\"1\":\"1\",\"2\":\"2\"}}', 0, 0, 0, 0, 0);
INSERT INTO `mac_group` VALUES (3, 'VIP会员', 1, ',1,6,7,8,9,10,11,12,2,13,14,15,16,3,4,5,17,18,', '{\"1\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"6\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"7\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"8\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"9\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"10\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"11\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"12\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"2\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"13\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"14\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"15\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"16\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"3\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"4\":{\"1\":\"1\",\"2\":\"2\",\"3\":\"3\",\"4\":\"4\",\"5\":\"5\"},\"5\":{\"1\":\"1\",\"2\":\"2\"},\"17\":{\"1\":\"1\",\"2\":\"2\"},\"18\":{\"1\":\"1\",\"2\":\"2\"}}', 10, 70, 300, 3600, 0);

-- ----------------------------
-- Table structure for mac_link
-- ----------------------------
DROP TABLE IF EXISTS `mac_link`;
CREATE TABLE `mac_link`  (
  `link_id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `link_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `link_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `link_sort` smallint(6) NOT NULL DEFAULT 0,
  `link_add_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `link_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `link_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `link_logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`) USING BTREE,
  INDEX `link_sort`(`link_sort`) USING BTREE,
  INDEX `link_type`(`link_type`) USING BTREE,
  INDEX `link_add_time`(`link_add_time`) USING BTREE,
  INDEX `link_time`(`link_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_link
-- ----------------------------

-- ----------------------------
-- Table structure for mac_msg
-- ----------------------------
DROP TABLE IF EXISTS `mac_msg`;
CREATE TABLE `mac_msg`  (
  `msg_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `msg_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `msg_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `msg_to` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `msg_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `msg_content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `msg_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`msg_id`) USING BTREE,
  INDEX `msg_code`(`msg_code`) USING BTREE,
  INDEX `msg_time`(`msg_time`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_msg
-- ----------------------------

-- ----------------------------
-- Table structure for mac_order
-- ----------------------------
DROP TABLE IF EXISTS `mac_order`;
CREATE TABLE `mac_order`  (
  `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `order_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `order_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `order_price` decimal(12, 2) UNSIGNED NOT NULL DEFAULT 0.00,
  `order_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `order_points` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `order_pay_type` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `order_pay_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `order_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`order_id`) USING BTREE,
  INDEX `order_code`(`order_code`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `order_time`(`order_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_order
-- ----------------------------

-- ----------------------------
-- Table structure for mac_plog
-- ----------------------------
DROP TABLE IF EXISTS `mac_plog`;
CREATE TABLE `mac_plog`  (
  `plog_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_id_1` int(10) NOT NULL DEFAULT 0,
  `plog_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `plog_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `plog_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `plog_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`plog_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `plog_type`(`plog_type`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_plog
-- ----------------------------

-- ----------------------------
-- Table structure for mac_role
-- ----------------------------
DROP TABLE IF EXISTS `mac_role`;
CREATE TABLE `mac_role`  (
  `role_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_rid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `role_lock` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `role_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_actor` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_sort` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `role_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `role_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `role_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `role_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `role_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_jumpurl` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `role_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`role_id`) USING BTREE,
  INDEX `role_rid`(`role_rid`) USING BTREE,
  INDEX `role_name`(`role_name`) USING BTREE,
  INDEX `role_en`(`role_en`) USING BTREE,
  INDEX `role_letter`(`role_letter`) USING BTREE,
  INDEX `role_actor`(`role_actor`) USING BTREE,
  INDEX `role_level`(`role_level`) USING BTREE,
  INDEX `role_time`(`role_time`) USING BTREE,
  INDEX `role_time_add`(`role_time_add`) USING BTREE,
  INDEX `role_score`(`role_score`) USING BTREE,
  INDEX `role_score_all`(`role_score_all`) USING BTREE,
  INDEX `role_score_num`(`role_score_num`) USING BTREE,
  INDEX `role_up`(`role_up`) USING BTREE,
  INDEX `role_down`(`role_down`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_role
-- ----------------------------

-- ----------------------------
-- Table structure for mac_short_vod
-- ----------------------------
DROP TABLE IF EXISTS `mac_short_vod`;
CREATE TABLE `mac_short_vod`  (
  `vodId` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vodName` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '视频名',
  `vodPic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '封面图',
  `vodPlayUrl` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '播放地址',
  `fileType` enum('up','url','awsS3') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'up' COMMENT '文件类型 up.本地上传 url.远程图片 awsS3.亚马逊s3',
  `likeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '假的用来显示的点赞数',
  `realLikeCount` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '真实点赞数',
  `sort` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '排序（倒叙）',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '状态 -1删除 0禁用 1正常',
  `createTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' COMMENT '创建时间',
  `updateTime` datetime NOT NULL DEFAULT '1000-01-01 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`vodId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '短视频表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_short_vod
-- ----------------------------
INSERT INTO `mac_short_vod` VALUES (1, '测试3323', '/Upload/Image/video/1000/01/01/8a230ff9ad3d20630e9c4cf58ca00116.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'awsS3', 1, 0, 100, 1, '1000-01-01 00:00:00', '2023-10-27 13:33:27');
INSERT INTO `mac_short_vod` VALUES (2, '测试3323', '/Upload/Image/video/1000/01/01/973e69156fdadb42591f7767897b0527.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'awsS3', 2, 0, 2, 1, '1000-01-01 00:00:00', '2023-10-27 14:04:49');
INSERT INTO `mac_short_vod` VALUES (3, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 3, 0, 3, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:26');
INSERT INTO `mac_short_vod` VALUES (4, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 4, 0, 4, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:26');
INSERT INTO `mac_short_vod` VALUES (5, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 325, 0, 5, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:28');
INSERT INTO `mac_short_vod` VALUES (6, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 6, 0, 6, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:28');
INSERT INTO `mac_short_vod` VALUES (7, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 7, 0, 7, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:30');
INSERT INTO `mac_short_vod` VALUES (8, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 8, 0, 8, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:30');
INSERT INTO `mac_short_vod` VALUES (9, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 9, 0, 9, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:32');
INSERT INTO `mac_short_vod` VALUES (10, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 32, 0, 10, 1, '1000-01-01 00:00:00', '2023-10-23 20:15:43');
INSERT INTO `mac_short_vod` VALUES (11, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 11, 0, 11, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:34');
INSERT INTO `mac_short_vod` VALUES (12, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 22, 0, 12, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:36');
INSERT INTO `mac_short_vod` VALUES (13, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 33, 0, 13, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:37');
INSERT INTO `mac_short_vod` VALUES (14, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 44, 0, 14, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:37');
INSERT INTO `mac_short_vod` VALUES (15, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 55, 0, 15, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:38');
INSERT INTO `mac_short_vod` VALUES (16, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 66, 0, 16, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:39');
INSERT INTO `mac_short_vod` VALUES (17, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 77, 0, 17, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:40');
INSERT INTO `mac_short_vod` VALUES (18, '测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 88, 0, 18, 1, '1000-01-01 00:00:00', '2023-10-23 20:18:41');
INSERT INTO `mac_short_vod` VALUES (19, '测试33231', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 99, 0, 19, 1, '1000-01-01 00:00:00', '2023-10-23 21:47:27');
INSERT INTO `mac_short_vod` VALUES (20, '112测试3323', 'https://img.kuaikanzy.net/upload/vod/20230328-1/e8ab7380b082c0d8fd341d1e6afc2918.jpg', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'url', 32, 0, 20, -1, '1000-01-01 00:00:00', '2023-10-24 13:05:41');
INSERT INTO `mac_short_vod` VALUES (21, 'test1', '/Upload/Image/video/2023/10/24/1a4b26db71e4b570357ebff3a18b880d.png', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'up', 10, 0, 20, 1, '2023-10-23 21:49:04', '2023-10-24 17:24:28');
INSERT INTO `mac_short_vod` VALUES (22, 'ttt33123', '/Upload/Image/video/2023/10/27/1bfb35abe03debe3f4e9ec4badcf8e76.png', 'https://vip.kuaikan-cdn1.com/20230831/9NaVrPXy/index.m3u8', 'awsS3', 1, 0, 0, 1, '2023-10-27 14:05:43', '2023-10-27 14:08:32');

-- ----------------------------
-- Table structure for mac_tmpvod
-- ----------------------------
DROP TABLE IF EXISTS `mac_tmpvod`;
CREATE TABLE `mac_tmpvod`  (
  `id1` int(10) UNSIGNED NULL DEFAULT NULL,
  `name1` varchar(1024) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT ''
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of mac_tmpvod
-- ----------------------------
INSERT INTO `mac_tmpvod` VALUES (442, '一剑独尊');
INSERT INTO `mac_tmpvod` VALUES (59, '万界独尊');
INSERT INTO `mac_tmpvod` VALUES (76, '五行战神');
INSERT INTO `mac_tmpvod` VALUES (147, '仙武帝尊');
INSERT INTO `mac_tmpvod` VALUES (1126, '公主的交换人生');
INSERT INTO `mac_tmpvod` VALUES (58, '冰火魔厨');
INSERT INTO `mac_tmpvod` VALUES (75, '剑骨');
INSERT INTO `mac_tmpvod` VALUES (203, '幸免于难 第一季');
INSERT INTO `mac_tmpvod` VALUES (53, '异次元爱情故事');
INSERT INTO `mac_tmpvod` VALUES (38, '快乐的大人');
INSERT INTO `mac_tmpvod` VALUES (209, '惊人的星期六');
INSERT INTO `mac_tmpvod` VALUES (282, '我独自生活');
INSERT INTO `mac_tmpvod` VALUES (410, '捐躯');
INSERT INTO `mac_tmpvod` VALUES (37, '朋友请吃饭');
INSERT INTO `mac_tmpvod` VALUES (341, '末世超级系统');
INSERT INTO `mac_tmpvod` VALUES (1015, '来日方长');
INSERT INTO `mac_tmpvod` VALUES (1103, '民宿的秘密佐料');
INSERT INTO `mac_tmpvod` VALUES (1007, '社畜向前冲');
INSERT INTO `mac_tmpvod` VALUES (353, '绝世战魂');
INSERT INTO `mac_tmpvod` VALUES (544, '邪恶新郎');
INSERT INTO `mac_tmpvod` VALUES (114, '长相思');
INSERT INTO `mac_tmpvod` VALUES (386, '阿衰 第九季');
INSERT INTO `mac_tmpvod` VALUES (380, '附身');

-- ----------------------------
-- Table structure for mac_topic
-- ----------------------------
DROP TABLE IF EXISTS `mac_topic`;
CREATE TABLE `mac_topic`  (
  `topic_id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `topic_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_sub` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `topic_sort` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `topic_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_pic_thumb` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_pic_slide` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_des` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_blurb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `topic_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `topic_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `topic_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `topic_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `topic_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `topic_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `topic_tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `topic_rel_vod` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `topic_rel_art` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `topic_content` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `topic_extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`topic_id`) USING BTREE,
  INDEX `topic_sort`(`topic_sort`) USING BTREE,
  INDEX `topic_level`(`topic_level`) USING BTREE,
  INDEX `topic_score`(`topic_score`) USING BTREE,
  INDEX `topic_score_all`(`topic_score_all`) USING BTREE,
  INDEX `topic_score_num`(`topic_score_num`) USING BTREE,
  INDEX `topic_hits`(`topic_hits`) USING BTREE,
  INDEX `topic_hits_day`(`topic_hits_day`) USING BTREE,
  INDEX `topic_hits_week`(`topic_hits_week`) USING BTREE,
  INDEX `topic_hits_month`(`topic_hits_month`) USING BTREE,
  INDEX `topic_time_add`(`topic_time_add`) USING BTREE,
  INDEX `topic_time`(`topic_time`) USING BTREE,
  INDEX `topic_time_hits`(`topic_time_hits`) USING BTREE,
  INDEX `topic_name`(`topic_name`) USING BTREE,
  INDEX `topic_en`(`topic_en`) USING BTREE,
  INDEX `topic_up`(`topic_up`) USING BTREE,
  INDEX `topic_down`(`topic_down`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_topic
-- ----------------------------

-- ----------------------------
-- Table structure for mac_type
-- ----------------------------
DROP TABLE IF EXISTS `mac_type`;
CREATE TABLE `mac_type`  (
  `type_id` smallint(6) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_en` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_sort` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `type_mid` smallint(6) UNSIGNED NOT NULL DEFAULT 1,
  `type_pid` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `type_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `type_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_tpl_list` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_tpl_detail` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_tpl_play` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_tpl_down` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_des` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_union` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `type_logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_pic` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `type_jumpurl` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`type_id`) USING BTREE,
  INDEX `type_sort`(`type_sort`) USING BTREE,
  INDEX `type_pid`(`type_pid`) USING BTREE,
  INDEX `type_name`(`type_name`) USING BTREE,
  INDEX `type_en`(`type_en`) USING BTREE,
  INDEX `type_mid`(`type_mid`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 62 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_type
-- ----------------------------
INSERT INTO `mac_type` VALUES (1, '电影', 'dianying', 1, 1, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\\u5927\\u9646,\\u9999\\u6e2f,\\u53f0\\u6e7e,\\u7f8e\\u56fd,\\u6cd5\\u56fd,\\u82f1\\u56fd,\\u65e5\\u672c,\\u97e9\\u56fd,\\u5fb7\\u56fd,\\u6cf0\\u56fd,\\u5370\\u5ea6,\\u610f\\u5927\\u5229,\\u897f\\u73ed\\u7259,\\u52a0\\u62ff\\u5927,\\u5176\\u4ed6\",\"lang\":\"\\u56fd\\u8bed,\\u82f1\\u8bed,\\u7ca4\\u8bed,\\u95fd\\u5357\\u8bed,\\u97e9\\u8bed,\\u65e5\\u8bed,\\u6cd5\\u8bed,\\u5fb7\\u8bed,\\u5176\\u4ed6\",\"year\":\"2023,2022,2021,2020,2019,2018,2017,2016,2015,2014,2013,2012,2011,2010\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (2, '连续剧', 'lianxuju', 2, 1, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\\u5927\\u9646,\\u97e9\\u56fd,\\u9999\\u6e2f,\\u53f0\\u6e7e,\\u65e5\\u672c,\\u7f8e\\u56fd,\\u6cf0\\u56fd,\\u82f1\\u56fd,\\u65b0\\u52a0\\u5761,\\u5176\\u4ed6\",\"lang\":\"\\u56fd\\u8bed,\\u82f1\\u8bed,\\u7ca4\\u8bed,\\u95fd\\u5357\\u8bed,\\u97e9\\u8bed,\\u65e5\\u8bed,\\u5176\\u4ed6\",\"year\":\"2023,2022,2021,2020,2019,2018,2017,2016,2015,2014,2013,2012,2011,2010\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (3, '综艺', 'zongyi', 3, 1, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\\u5927\\u9646,\\u9999\\u6e2f,\\u53f0\\u6e7e,\\u7f8e\\u56fd,\\u6cd5\\u56fd,\\u82f1\\u56fd,\\u65e5\\u672c,\\u97e9\\u56fd,\\u5fb7\\u56fd,\\u6cf0\\u56fd,\\u5370\\u5ea6,\\u610f\\u5927\\u5229,\\u897f\\u73ed\\u7259,\\u52a0\\u62ff\\u5927,\\u5176\\u4ed6\",\"lang\":\"\\u56fd\\u8bed,\\u82f1\\u8bed,\\u7ca4\\u8bed,\\u95fd\\u5357\\u8bed,\\u97e9\\u8bed,\\u65e5\\u8bed,\\u6cd5\\u8bed,\\u5fb7\\u8bed,\\u5176\\u4ed6\",\"year\":\"2023,2022,2021,2020,2019,2018,2017,2016,2015,2014,2013,2012,2011,2010\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (4, '动漫', 'dongman', 4, 1, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\\u5927\\u9646,\\u9999\\u6e2f,\\u53f0\\u6e7e,\\u7f8e\\u56fd,\\u6cd5\\u56fd,\\u82f1\\u56fd,\\u65e5\\u672c,\\u97e9\\u56fd,\\u5fb7\\u56fd,\\u6cf0\\u56fd,\\u5370\\u5ea6,\\u610f\\u5927\\u5229,\\u897f\\u73ed\\u7259,\\u52a0\\u62ff\\u5927,\\u5176\\u4ed6\",\"lang\":\"\\u56fd\\u8bed,\\u82f1\\u8bed,\\u7ca4\\u8bed,\\u95fd\\u5357\\u8bed,\\u97e9\\u8bed,\\u65e5\\u8bed,\\u6cd5\\u8bed,\\u5fb7\\u8bed,\\u5176\\u4ed6\",\"year\":\"2023,2022,2021,2020,2019,2018,2017,2016,2015,2014,2013,2012,2011,2010\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (5, '体育赛事', 'tiyusaishi', 5, 1, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (6, '动作片', 'dongzuopian', 1, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (7, '喜剧片', 'xijupian', 2, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (8, '爱情片', 'aiqingpian', 3, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (9, '科幻片', 'kehuanpian', 4, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (10, '恐怖片', 'kongbupian', 5, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (11, '剧情片', 'juqingpian', 6, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (12, '战争片', 'zhanzhengpian', 7, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (13, '纪录片', 'jilupian', 8, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (14, '伦理片', 'lunlipian', 9, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (15, '动漫电影', 'dongmandianying', 10, 1, 1, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (16, '国产剧', 'guochanju', 1, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (17, '港澳剧', 'gangaoju', 2, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (18, '日剧', 'riju', 3, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (19, '欧美剧', 'oumeiju', 4, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (20, '台湾剧', 'taiwanju', 5, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (21, '泰剧', 'taiju', 6, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (22, '韩剧', 'hanju', 7, 1, 2, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (23, '小说', 'xiaoshuo', 1, 2, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (24, '精品小说', 'jingpinxiaoshuo', 10, 2, 23, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (25, '精品美图', 'jingpinmeitu', 20, 2, 23, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (26, '演员', 'yanyuan', 20, 8, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (27, '福利', 'fuli', 100, 16, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (28, '精选', 'jingxuan', 20, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (29, '日韩', 'rihan', 30, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (30, '欧美', 'oumei', 40, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (31, '国产', 'guochan', 50, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (32, '动漫', 'dongman', 60, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (33, '美女主播', 'meinvzhubo', 70, 16, 27, 0, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (34, '传媒', 'chuanmei', 80, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (35, '国产精品', 'guochanjingpin', 90, 16, 27, 0, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (36, '国产动漫', 'guochandongman', 1, 1, 4, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (37, '日韩动漫', 'rihandongman', 2, 1, 4, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (38, '欧美动漫', 'oumeidongman', 3, 1, 4, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (39, '港台动漫', 'gangtaidongman', 4, 1, 4, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (40, '海外动漫', 'haiwaidongman', 5, 1, 4, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (41, '综艺', 'zongyi', 1, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (42, '真人秀', 'zhenrenxiu', 2, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (43, '脱口秀', 'tuokouxiu', 3, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (44, '音乐', 'yinyue', 4, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (45, '选秀', 'xuanxiu', 5, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (46, '其他', 'qita', 6, 1, 3, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (47, '今日更新', 'jinrigengxin', 10, 16, 27, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', '{\"class\":\"\",\"area\":\"\",\"lang\":\"\",\"year\":\"\",\"star\":\"\",\"director\":\"\",\"state\":\"\",\"version\":\"\"}', '', '', '');
INSERT INTO `mac_type` VALUES (48, '首页福利', 'shouyefuli', 0, 16, 0, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (49, '热门', 'remen', 10, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (50, '麻豆', 'madou', 20, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (51, '九一', 'jiuyi', 30, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (52, '皇家', 'huangjia', 40, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (53, '蜜桃', 'mitao', 50, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');
INSERT INTO `mac_type` VALUES (54, '精东', 'jingdong', 60, 16, 48, 1, 'type.html', 'show.html', 'detail.html', 'play.html', 'down.html', '', '', '', '', NULL, '', '', '');

-- ----------------------------
-- Table structure for mac_ulog
-- ----------------------------
DROP TABLE IF EXISTS `mac_ulog`;
CREATE TABLE `mac_ulog`  (
  `ulog_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_mid` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_type` tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  `ulog_rid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_sid` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_nid` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `ulog_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`ulog_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `ulog_mid`(`ulog_mid`) USING BTREE,
  INDEX `ulog_type`(`ulog_type`) USING BTREE,
  INDEX `ulog_rid`(`ulog_rid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = FIXED;

-- ----------------------------
-- Records of mac_ulog
-- ----------------------------

-- ----------------------------
-- Table structure for mac_user
-- ----------------------------
DROP TABLE IF EXISTS `mac_user`;
CREATE TABLE `mac_user`  (
  `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `group_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `user_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_pwd` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_nick_name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_qq` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_phone` varchar(16) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `user_portrait` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_portrait_thumb` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_openid_qq` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_openid_weixin` varchar(40) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_question` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_answer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_points` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_points_froze` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_reg_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_reg_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_login_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_login_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_last_login_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_last_login_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_login_num` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `user_extend` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `user_random` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `user_end_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_pid` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_pid_2` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `user_pid_3` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`user_id`) USING BTREE,
  INDEX `type_id`(`group_id`) USING BTREE,
  INDEX `user_name`(`user_name`) USING BTREE,
  INDEX `user_reg_time`(`user_reg_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_user
-- ----------------------------

-- ----------------------------
-- Table structure for mac_visit
-- ----------------------------
DROP TABLE IF EXISTS `mac_visit`;
CREATE TABLE `mac_visit`  (
  `visit_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NULL DEFAULT 0,
  `visit_ip` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `visit_ly` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `visit_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`visit_id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `visit_time`(`visit_time`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_visit
-- ----------------------------

-- ----------------------------
-- Table structure for mac_vod
-- ----------------------------
DROP TABLE IF EXISTS `mac_vod`;
CREATE TABLE `mac_vod`  (
  `vod_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` smallint(6) NOT NULL DEFAULT 0,
  `type_id_1` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `group_id` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `vod_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_sub` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_tag` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_class` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pic_thumb` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pic_slide` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pic_screenshot` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `vod_actor` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_director` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_writer` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_behind` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_blurb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pubdate` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_total` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_serial` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `vod_tv` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_weekday` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_area` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_year` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_version` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_state` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_author` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_jumpurl` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_tpl_play` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_tpl_down` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_isend` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_lock` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_copyright` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_points` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `vod_points_play` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `vod_points_down` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `vod_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_duration` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `vod_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `vod_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `vod_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `vod_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `vod_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `vod_trysee` smallint(6) UNSIGNED NOT NULL DEFAULT 0,
  `vod_douban_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `vod_douban_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `vod_reurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_rel_vod` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_rel_art` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd_play` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd_play_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd_down` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_pwd_down_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `vod_play_from` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_play_server` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_play_note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_play_url` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `vod_down_from` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_down_server` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_down_note` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `vod_down_url` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `vod_plot` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `vod_plot_name` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `vod_plot_detail` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`vod_id`) USING BTREE,
  INDEX `type_id`(`type_id`) USING BTREE,
  INDEX `type_id_1`(`type_id_1`) USING BTREE,
  INDEX `vod_level`(`vod_level`) USING BTREE,
  INDEX `vod_hits`(`vod_hits`) USING BTREE,
  INDEX `vod_letter`(`vod_letter`) USING BTREE,
  INDEX `vod_name`(`vod_name`) USING BTREE,
  INDEX `vod_year`(`vod_year`) USING BTREE,
  INDEX `vod_area`(`vod_area`) USING BTREE,
  INDEX `vod_lang`(`vod_lang`) USING BTREE,
  INDEX `vod_tag`(`vod_tag`) USING BTREE,
  INDEX `vod_class`(`vod_class`) USING BTREE,
  INDEX `vod_lock`(`vod_lock`) USING BTREE,
  INDEX `vod_up`(`vod_up`) USING BTREE,
  INDEX `vod_down`(`vod_down`) USING BTREE,
  INDEX `vod_en`(`vod_en`) USING BTREE,
  INDEX `vod_hits_day`(`vod_hits_day`) USING BTREE,
  INDEX `vod_hits_week`(`vod_hits_week`) USING BTREE,
  INDEX `vod_hits_month`(`vod_hits_month`) USING BTREE,
  INDEX `vod_plot`(`vod_plot`) USING BTREE,
  INDEX `vod_points_play`(`vod_points_play`) USING BTREE,
  INDEX `vod_points_down`(`vod_points_down`) USING BTREE,
  INDEX `group_id`(`group_id`) USING BTREE,
  INDEX `vod_time_add`(`vod_time_add`) USING BTREE,
  INDEX `vod_time`(`vod_time`) USING BTREE,
  INDEX `vod_time_make`(`vod_time_make`) USING BTREE,
  INDEX `vod_actor`(`vod_actor`) USING BTREE,
  INDEX `vod_director`(`vod_director`) USING BTREE,
  INDEX `vod_score_all`(`vod_score_all`) USING BTREE,
  INDEX `vod_score_num`(`vod_score_num`) USING BTREE,
  INDEX `vod_total`(`vod_total`) USING BTREE,
  INDEX `vod_score`(`vod_score`) USING BTREE,
  INDEX `vod_version`(`vod_version`) USING BTREE,
  INDEX `vod_state`(`vod_state`) USING BTREE,
  INDEX `vod_isend`(`vod_isend`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_vod
-- ----------------------------

-- ----------------------------
-- Table structure for mac_vod_search
-- ----------------------------
DROP TABLE IF EXISTS `mac_vod_search`;
CREATE TABLE `mac_vod_search`  (
  `search_key` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT '搜索键（关键词md5）',
  `search_word` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '搜索关键词',
  `search_field` varchar(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT '搜索字段名（可有多个，用|分隔）',
  `search_hit_count` bigint(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT '搜索命中次数',
  `search_last_hit_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '最近命中时间',
  `search_update_time` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '添加时间',
  `search_result_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '结果Id数量',
  `search_result_ids` mediumtext CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT '搜索结果Id列表，英文半角逗号分隔',
  PRIMARY KEY (`search_key`) USING BTREE,
  INDEX `search_field`(`search_field`) USING BTREE,
  INDEX `search_update_time`(`search_update_time`) USING BTREE,
  INDEX `search_hit_count`(`search_hit_count`) USING BTREE,
  INDEX `search_last_hit_time`(`search_last_hit_time`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = 'vod搜索缓存表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_vod_search
-- ----------------------------

-- ----------------------------
-- Table structure for mac_website
-- ----------------------------
DROP TABLE IF EXISTS `mac_website`;
CREATE TABLE `mac_website`  (
  `website_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type_id` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `type_id_1` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `website_name` varchar(60) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_sub` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_en` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_status` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `website_letter` char(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_color` varchar(6) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_lock` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `website_sort` int(10) NOT NULL DEFAULT 0,
  `website_jumpurl` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_pic` varchar(1024) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_pic_screenshot` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `website_logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_area` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_lang` varchar(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_level` tinyint(1) UNSIGNED NOT NULL DEFAULT 0,
  `website_time` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `website_time_add` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `website_time_hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `website_time_make` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `website_time_referer` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `website_hits` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_hits_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_hits_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_hits_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_score` decimal(3, 1) UNSIGNED NOT NULL DEFAULT 0.0,
  `website_score_all` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_score_num` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_up` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_down` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_referer` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_referer_day` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_referer_week` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_referer_month` mediumint(8) UNSIGNED NOT NULL DEFAULT 0,
  `website_tag` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_class` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_remarks` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_tpl` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_blurb` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
  `website_content` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`website_id`) USING BTREE,
  INDEX `type_id`(`type_id`) USING BTREE,
  INDEX `type_id_1`(`type_id_1`) USING BTREE,
  INDEX `website_name`(`website_name`) USING BTREE,
  INDEX `website_en`(`website_en`) USING BTREE,
  INDEX `website_letter`(`website_letter`) USING BTREE,
  INDEX `website_sort`(`website_sort`) USING BTREE,
  INDEX `website_lock`(`website_lock`) USING BTREE,
  INDEX `website_time`(`website_time`) USING BTREE,
  INDEX `website_time_add`(`website_time_add`) USING BTREE,
  INDEX `website_time_referer`(`website_time_referer`) USING BTREE,
  INDEX `website_hits`(`website_hits`) USING BTREE,
  INDEX `website_hits_day`(`website_hits_day`) USING BTREE,
  INDEX `website_hits_week`(`website_hits_week`) USING BTREE,
  INDEX `website_hits_month`(`website_hits_month`) USING BTREE,
  INDEX `website_time_make`(`website_time_make`) USING BTREE,
  INDEX `website_score`(`website_score`) USING BTREE,
  INDEX `website_score_all`(`website_score_all`) USING BTREE,
  INDEX `website_score_num`(`website_score_num`) USING BTREE,
  INDEX `website_up`(`website_up`) USING BTREE,
  INDEX `website_down`(`website_down`) USING BTREE,
  INDEX `website_level`(`website_level`) USING BTREE,
  INDEX `website_tag`(`website_tag`) USING BTREE,
  INDEX `website_class`(`website_class`) USING BTREE,
  INDEX `website_referer`(`website_referer`) USING BTREE,
  INDEX `website_referer_day`(`website_referer_day`) USING BTREE,
  INDEX `website_referer_week`(`website_referer_week`) USING BTREE,
  INDEX `website_referer_month`(`website_referer_month`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of mac_website
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
