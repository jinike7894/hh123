
<?php

namespace App\Service\Banner;

use App\Enum\RedisDb;
use App\Model\Article\BannerModel;
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

    // public function addArticle($data)
    // {
    //     try {
    //         DbManager::getInstance()->startTransactionWithCount();

    //         $articleId = ArticleModel::create($data)->save();

    //         if ($articleId === false) {
    //             throw new Exception('文章添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
    //         }

    //         // 添加完成后处理tag
    //         if ($data['tagList']) {
    //             $data['tagList'] = explode(',', $data['tagList']);

    //             foreach ($data['tagList'] as $datum) {
    //                 $this->relateTag($articleId, trim($datum));
    //             }
    //         }

    //         DbManager::getInstance()->commitWithCount();
    //     } catch (Throwable  $e) {
    //         DbManager::getInstance()->rollbackWithCount();
    //         throw new Exception($e->getMessage(), $e->getCode());
    //     }

    //     return $articleId;
    // }

    //前台获取轮播
    public function getBanner(){
        
    }


 


}