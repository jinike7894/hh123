<?php

namespace App\Model\Navigation;

use App\Model\BaseModel;
use App\RedisKey\Navigation\TemplateKey;
use EasySwoole\RedisPool\RedisPool;

/**
 * Class PageTemplateModel
 * @package App\Model\Navigation
 * @property $pageTemplateId int | id
 * @property $pageTemplateName string | 模板名
 * @property $pageTemplateKey string | 模板键
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class PageTemplateModel extends BaseModel
{
    protected $tableName = 'nav_page_template';

    protected $primaryKey = 'pageTemplateId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    /**
     * 常规情况下，页面模板的表是不会被编辑的，基本不会变。
     * 所以后台并没有删除这个缓存的地方，仅作为调用的时候不走数据库而缓存。
     * @param $pageTemplateId
     * @return PageTemplateModel|array|bool|\EasySwoole\ORM\AbstractModel|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|mixed|null
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getByCache($pageTemplateId)
    {
        $key = TemplateKey::cache($pageTemplateId);
        $redis = RedisPool::defer();
        $data = $redis->get($key);

        if ($data) {
            $data = unserialize($data);
        } else {
            $data = $this->get($pageTemplateId);
            $data && $redis->set($key, serialize($data), 600);
        }

        return $data;
    }
}