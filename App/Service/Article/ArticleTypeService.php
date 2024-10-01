<?php

namespace App\Service\Article;

use App\Model\Article\ArticleTypeModel;
use EasySwoole\Component\Singleton;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

class ArticleTypeService
{
    use Singleton;

    public function addArticleType($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $articleTypeId = ArticleTypeModel::create($data)->save();

            if ($articleTypeId === false) {
                throw new Exception('文章分类添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $articleTypeId;
    }

    public function editArticleType($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $articleType = ArticleTypeModel::create()->get([
                'articleTypeId' => $data['articleTypeId'],
                'status' => [ArticleTypeModel::STATE_DELETED, '>'],
            ]);

            if (!$articleType) {
                throw new Exception('无效的文章分类id', Status::CODE_BAD_REQUEST);
            }

            $result = $articleType->update($data);

            if ($result === false) {
                throw new Exception('文章分类修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }
}