<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

class BaseModel extends AbstractModel
{
    public function getAll($page = 1, $keyword = [], $pageSize = 20, $field = ['*'])
    {
        $where = $this->parseKeywordToWhere($keyword);

        $list = $this
            ->limit($pageSize * ($page - 1), $pageSize)
            ->withTotalCount()
            ->field($field)
            ->where($where)
            ->all();

        $total = $this->lastQueryResult()->getTotalCount();

        // return ['total' => $total, 'list' => $list, 'options' => $keyword, 'query' => $this->lastQuery()->getLastQuery()];
        return ['total' => $total, 'list' => $list, 'options' => $keyword];
    }

    public function getPk()
    {
        return $this->primaryKey;
    }

    /**
     * 设置默认排序
     * @return $this
     */
    public function setDefaultOrder()
    {
        $this->order($this->primaryKey, 'DESC');
        return $this;
    }

    /**
     * 设置排序类型，如果不是公共排序规则则在各自的类中实现。
     * @param $sortType
     * @return $this
     */
    public function setOrderType($sortType)
    {
        if ($sortType) {
            if (is_string($sortType)) {
                $sortType = explode('_', $sortType);
            }
            $this->order(...$sortType);
        } else {
            $this->setDefaultOrder();
        }

        return $this;
    }

    /**
     * 参数转where数组
     * @param $keyword
     * @return mixed
     */
    public function parseKeywordToWhere($keyword)
    {
        // 子类里面各管各的，目前没有公共的。
        // 提示，键值顺序决定where条件的顺序，请注意SQL细节。
        return $keyword;
    }

    /**
     * 直接通过keyword设置where
     * @param $keyword
     * @return $this
     */
    public function setKeyWord($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);
        $where && $this->where($where);
        return $this;
    }

    /**
     * 添加关联表数据
     * @param array $list
     * @param array $appendFieldList 要添加的字段列表
     * @param string $relationKey 数组里面的关联键
     * @param string $relationField 取数据的字段名
     * @param array $extraWhere 额外的条件
     * @return array|null
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function appendInfo(array $list, array $appendFieldList, string $relationKey, string $relationField, array $extraWhere = [])
    {
        if (!$list) {
            return $list;
        }

        $relationKeyList = array_unique(array_column($list, $relationKey));

        $targetList = [];
        if ($relationKeyList) {
            $extraWhere && $this->where($extraWhere);

            $targetList = $this
                ->field(array_merge($appendFieldList, [$relationField]))
                ->where($relationField, $relationKeyList, 'IN')
                ->all();

            $targetList = array_column($targetList, null, $relationField);
        }

        foreach ($list as &$item) {
            $relationValue = $item[$relationKey];

            // 方法1
            // 如果外键查出来没数据的话，比如真删除，那么就会导致结构不一致的情况。
            /*if (isset($targetList[$relationValue])) {
                $item = array_merge($item, $targetList[$relationValue]);
            }*/

            // 方法2
            // 这样写会必然会有这个键，只是值为空，保证数据结构一致。
            foreach ($appendFieldList as $appendFieldItem) {
                $item[$appendFieldItem] = isset($targetList[$relationValue][$appendFieldItem]) ? $targetList[$relationValue][$appendFieldItem] : '';
            }
        }

        return $list;
    }
}