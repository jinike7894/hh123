<?php

namespace App\Model\Navigation;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\RedisKey\Navigation\PageKey;
use EasySwoole\RedisPool\RedisPool;

/**
 * Class PageModel
 * @package App\Model\Navigation
 * @property $pageId int | id
 * @property $pageName string | 页面名
 * @property $pageTemplateId int | 模板id
 * @property $code string | 统计代码
 * @property $description string | 描述
 * @property $statisticEnabled int | 统计代码控制 1.开 0.关
 * @property $statisticConfig string | 统计代码控制配置
 * @property $latestTime datetime | 最后生成时间
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class PageModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'nav_page';

    protected $primaryKey = 'pageId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function getByCache($pageName)
    {
        $key = PageKey::cache($pageName);
        $redis = RedisPool::defer();
        $data = $redis->get($key);

        if ($data) {
            $data = unserialize($data);
        } else {
            $data = $this->get(['pageName' => $pageName]);
            $data && $redis->set($key, serialize($data), 600);
        }

        return $data;
    }
}