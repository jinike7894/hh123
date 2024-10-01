ALTER TABLE `art_article`
    ADD COLUMN `articleTypeId` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文章分类id' AFTER `articleId`,
ADD COLUMN `articleGroupKey` enum('PornNews','Muckraking') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PornNews' COMMENT '文章分组Key\r\n1.PornNews 性闻\r\n2.Muckraking 吃瓜爆料' AFTER `articleTypeId`,
ADD INDEX(`articleTypeId`),
ADD INDEX(`articleGroupKey`);