<?php

namespace App\Model\Article;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class TagModel
 * @package App\Model\Article
 * @property $tagId int | 标签id
 * @property $tagName int | 标签名
 * @property $sort int | 排序
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class TagModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'art_tag';

    protected $primaryKey = 'tagId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        // 虽然我很不想在左边加百分号，但是想着总共数据也不多，操作会更方便，还是加上的吧。
        isset($keyword['tagName']) && $keyword['tagName'] && $where['tagName'] = ['%' . $keyword['tagName'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    public function setOrderType($sortType){
        if ($sortType) {
            $sortType = explode('_', $sortType);
            $this->order(...$sortType);

            if ($sortType[0] == 'sort') {
                // 如果选择的是按照sort排序，那么依然要将id DESC作为第二排序
                $this->setDefaultOrder();
            }
        } else {
            $this->setDefaultOrder();
        }

        return $this;
    }

    /**
     * 检查name是否已存在
     * @param $tagName
     * @return bool
     * @throws \EasySwoole\Mysqli\Exception\Exception
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function checkNameExists($tagName)
    {
        if ($tagName) {
            return !!$this->where(['tagName' => $tagName, 'status' => [self::STATE_DELETED, '<>']])->val($this->primaryKey);
        } else {
            return false;
        }
    }
}