<?php

namespace App\Model\Article;

use App\Model\BaseModel;

/**
 * Class ArticleTagRelationModel
 * @package App\Model\Article
 * @property $articleId int | 文章id
 * @property $tagId int | 标签id
 */
class ArticleTagRelationModel extends BaseModel
{
    protected $tableName = 'art_article_tag_relation';

    protected $primaryKey = ['articleId', 'tagId'];
}