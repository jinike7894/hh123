<?php

namespace App\RedisKey\Article;

class ArticleKey
{
    public static function articleRead($userId, $articleId)
    {
        return 'ArticleRead_' . $userId . '_' . $articleId;
    }

    public static function articleLike($userId, $articleId)
    {
        return 'ArticleLike_' . $userId . '_' . $articleId;
    }
}