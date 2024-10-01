<?php

namespace App\Service\Article;

use App\Model\Article\ArticleTagRelationModel;
use App\Model\Article\TagModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class TagService
{
    use Singleton;

    public function addTag($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $existing = TagModel::create()->checkNameExists($data['tagName']);

            if ($existing) {
                throw new Exception('该标签已存在', Status::CODE_BAD_REQUEST);
            }

            $tagId = TagModel::create($data)->save();

            if ($tagId === false) {
                throw new Exception('标签添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $tagId;
    }

    public function editTag($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $tag = TagModel::create()->get($data['tagId']);

            if (!$tag) {
                throw new Exception('无效的标签id', Status::CODE_BAD_REQUEST);
            }

            if (isset($data['tagName'])) {
                $existing = TagModel::create()
                    ->where(['tagId' => [$data['tagId'], '<>']])
                    ->checkNameExists($data['tagName']);

                if ($existing) {
                    throw new Exception('该标签已存在', Status::CODE_BAD_REQUEST);
                }
            }

            $result = $tag->update($data);

            if ($result === false) {
                throw new Exception('标签修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 删除标签同时删除文章与标签的关联关系
     * @param $data
     * @return bool
     * @throws Throwable
     */
    public function deleteTag($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $tag = TagModel::create()->get($data['tagId']);

            if (!$tag) {
                throw new Exception('无效的标签id', Status::CODE_BAD_REQUEST);
            }

            $result = $tag->destroy();

            if ($result === false) {
                throw new Exception('标签删除出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $result = ArticleTagRelationModel::create()
                ->where(['tagId' => $data['tagId']])
                ->destroy();

            if ($result === false) {
                throw new Exception('文章标签关系删除出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 用tab名模糊搜索相关联的文章id
     * @param $tagName
     * @param int $limit
     * @return array|null
     * @throws Throwable
     * @throws \EasySwoole\ORM\Exception\Exception
     */
    public function searchRelatedArticleIdList($tagName, $limit = 200)
    {
        $tagIdList = TagModel::create()
            ->where([
                'tagName' => ["%{$tagName}%", 'LIKE'],
                'status' => TagModel::STATE_NORMAL,
            ])
            ->column('tagId');

        if (!$tagIdList) {
            return [];
        }

        $atr = ArticleTagRelationModel::create();

        $limit && $atr->limit($limit);

        return $atr
            ->where(['tagId' => [$tagIdList, 'IN']])
            ->column('articleId');
    }
}