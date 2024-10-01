<?php

namespace App\Model\Navigation;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\RedisKey\Navigation\AdGroupKey;
use EasySwoole\RedisPool\RedisPool;

/**
 * Class AdGroupModel
 * @package App\Model\Navigation
 * @property $adGroupId int | id
 * @property $adGroupName string | 广告组名
 * @property $adGroupAlias string | 广告组别名（前台显示值）
 * @property $adGroupKey string | 广告组识别key
 * @property $extensionFields string | 扩展字段（JSON字符串）
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class AdGroupModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'nav_ad_group';

    protected $primaryKey = 'adGroupId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    public function getList($keyword)
    {
        $this->setKeyWord($keyword);

        $dataList = $this
            ->field(['adGroupId', 'adGroupName', 'adGroupAlias', 'adGroupKey', 'extensionFields'])
            ->order('sort', 'ASC')
            ->all();

        foreach ($dataList as &$item) {
            $item = $item->toRawArray();
            $item['extensionFields'] = $item['extensionFields'] ? json_decode($item['extensionFields'], true) : [];
        }

        return $dataList;
    }

    public function getExtensionFields($adGroupId)
    {
        $redis = RedisPool::defer();
        $key = AdGroupKey::extension($adGroupId);

        $data = $redis->get($key);

        if (!$data) {
            $extensionFields = $this->where(['adGroupId' => $adGroupId])->val('extensionFields');
            $data = json_decode($extensionFields, true);
            $redis->set($key, $data, 600);
        }

        return $data;
    }

    public function appendGroupInfo($list)
    {
        if (!$list) {
            return $list;
        }

        $list = array_column($list, null, 'adId');
        $adIdList = array_keys($list);

        $relationList = [];
        if ($adIdList) {
            $relationList = AdGroupRelationModel::create()
                ->field(['adId', 'adGroupId', 'sort'])
                ->where(['adId' => [$adIdList, 'IN']])
                ->all();
        }

        $groupInfo = $this
            ->field(['adGroupId', 'adGroupName'])
            ->indexBy('adGroupId');

        foreach ($relationList as $relation) {
            $mergeGroupInfo = array_merge($groupInfo[$relation['adGroupId']], ['sort' => $relation['sort']]);

            if (isset($list[$relation['adId']]['adGroup'])) {
                // Indirect modification of overloaded element of XXX has no effect
                $temp = array_merge($list[$relation['adId']]['adGroup'], [$mergeGroupInfo]);
                $list[$relation['adId']]['adGroup'] = $temp;
            } else {
                $list[$relation['adId']]['adGroup'] = [$mergeGroupInfo];
            }
        }

        return array_values($list);
    }
}