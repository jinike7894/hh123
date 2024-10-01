<?php

namespace App\Service\Article;

use App\Enum\RedisDb;
use App\Model\Article\ArticleModel;
use App\Model\Article\ArticleTagRelationModel;
use App\Model\Article\TagModel;
use App\RedisKey\Article\ArticleKey;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

class ArticleService
{
    use Singleton;

    public function addArticle($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $articleId = ArticleModel::create($data)->save();

            if ($articleId === false) {
                throw new Exception('文章添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            // 添加完成后处理tag
            if ($data['tagList']) {
                $data['tagList'] = explode(',', $data['tagList']);

                foreach ($data['tagList'] as $datum) {
                    $this->relateTag($articleId, trim($datum));
                }
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $articleId;
    }

    public function editArticle($data, $withTag = true)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $article = ArticleModel::create()->get([
                'articleId' => $data['articleId'],
                'status' => [ArticleModel::STATE_DELETED, '>'],
            ]);

            if (!$article) {
                throw new Exception('无效的文章id', Status::CODE_BAD_REQUEST);
            }

            # TODO: 判断删除失效的图片

            $result = $article->update($data);

            if ($result === false) {
                throw new Exception('文章修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            if ($withTag) {
                // 不管怎么样，编辑都要先删除之前的tag关系
                ArticleTagRelationModel::create()->where(['articleId' => $article->articleId])->destroy();

                // 添加完成后处理tag
                if (isset($data['tagList']) && $data['tagList']) {
                    $data['tagList'] = explode(',', $data['tagList']);

                    foreach ($data['tagList'] as $datum) {
                        $this->relateTag($article->articleId, trim($datum));
                    }
                }
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 文章关联tag
     * @param $articleId
     * @param $tagName
     * @return bool
     * @throws Throwable
     */
    public function relateTag($articleId, $tagName)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $tag = TagModel::create()->get(['tagName' => $tagName]);

            if (!$tag) {
                $tag = TagModel::create(['tagName' => $tagName]);
                $tag->save();
            }

            $result = ArticleTagRelationModel::create(['articleId' => $articleId, 'tagId' => $tag->tagId])->save();

            if ($result === false) {
                throw new Exception('文章标签关联添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    public function read($userId, $articleId)
    {
        try {
            $key = ArticleKey::articleRead($userId, $articleId);
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);

            $isAlreadyRead = $redis->exists($key);
            if ($isAlreadyRead) {
                return false;
            }

            DbManager::getInstance()->startTransactionWithCount();

            $result = ArticleModel::create()
                ->where(['articleId' => $articleId])
                ->update([
                    'realReadCount' => QueryBuilder::inc(),
                ]);

            if ($result === false) {
                throw new Exception('文章阅读添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            // 3600秒内同一用户阅读只计算一次
            $redis->setEx($key, 3600, 1);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 是否已经喜欢(存在Key)
     * @param $userId
     * @param $articleId
     * @return false|string
     */
    public function isAlreadyLike($userId, $articleId)
    {
        $key = ArticleKey::articleLike($userId, $articleId);
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        return $redis->exists($key);
    }

    /**
     * 点赞
     * @param $userId
     * @param $articleId
     * @return bool
     * @throws Throwable
     */
    public function like($userId, $articleId)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $result = ArticleModel::create()
                ->where(['articleId' => $articleId])
                ->update([
                    'realLikeCount' => QueryBuilder::inc(),
                ]);

            if ($result === false) {
                throw new Exception('文章点赞添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $key = ArticleKey::articleLike($userId, $articleId);
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);

            // 3600秒内同一用户点赞只计算一次
            $redis->setEx($key, 3600, 1);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 取消点赞
     * @param $userId
     * @param $articleId
     * @return bool
     * @throws Throwable
     */
    public function dislike($userId, $articleId)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $result = ArticleModel::create()
                ->where(['articleId' => $articleId])
                ->update([
                    'realLikeCount' => QueryBuilder::dec(),
                ]);

            if ($result === false) {
                throw new Exception('文章点赞取消出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $key = ArticleKey::articleLike($userId, $articleId);
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);

            // 取消点赞则删除缓存键，以便再次点赞。
            $redis->del($key);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}