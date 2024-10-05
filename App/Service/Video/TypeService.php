<?php

namespace App\Service\Video;

use App\Model\Video\TypeModel;
use App\Model\Video\VideoModel;
use App\RedisKey\Video\TypeKey;
use EasySwoole\Component\Singleton;
use EasySwoole\RedisPool\RedisPool;

class TypeService
{
    use Singleton;

    /**
     * 获取av分类列表，不需要分页。
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|string
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getAdultVideoTypeList($module)
    {
        $redis = RedisPool::defer();
        $key = TypeKey::AdultTypeList($module);
        $data = $redis->get($key);

        if (!$data) {

            $typeEn = TypeModel::MODULE_TYPE_EN[$module];
            $pid = TypeModel::create()->where(['type_en' => $typeEn])->val('type_id');

            $data = TypeModel::create()
                ->field(['type_id AS typeId', 'type_name AS typeName',"is_free"])
                ->where([
                    'type_mid' => TypeModel::MID_ADULT_VIDEO,
                    'type_pid' => $pid,
                    'type_status' => TypeModel::STATE_NORMAL,
                ])
                ->order('type_sort', 'ASC')
                ->all();

            $redis->set($key, $data, 120);
        }

        return $data;
    }
    public function getAdultVideoHotTypeList($module)
    {
        $redis = RedisPool::defer();
        $key = TypeKey::AdultTypeList($module);
        $data = $redis->get($key);
        if (!$data) {
            $typeEn = TypeModel::MODULE_TYPE_EN[$module];
            $pid = TypeModel::create()->where(['type_en' => $typeEn])->val('type_id');

            $data = TypeModel::create()
                ->field(['type_id AS typeId', 'type_name AS typeName'])
                ->where([
                    'type_mid' => TypeModel::MID_ADULT_VIDEO,
                    'type_pid' => $pid,
                    'type_status' => TypeModel::STATE_NORMAL,
                ])
                ->order('type_sort', 'DESC')
                ->all();
            $redis->set($key, $data, 120);
        }

        return $data;
    }
    /**
     * 获取传媒二级分类列表，不需要分页。
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface|string
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getTopSubList($type_en)
    {
        if($type_en == 'top_chuanmei'){
            $type_en = 'shouyefuli';
        }
        $pid = TypeModel::create()->where(['type_en' => $type_en])->val('type_id');
        $data = TypeModel::create()
            ->field(['type_id AS typeId', 'type_name AS typeName'])
            ->where([
                'type_mid' => TypeModel::MID_ADULT_VIDEO,
                'type_pid' => $pid,
                'type_status' => TypeModel::STATE_NORMAL,
            ])
            ->order('type_sort', 'ASC')
            ->all();

        $result = [];
        foreach ($data as $val){
            $typeRecommendedList = VideoModel::create()->limit(12)->getRecommendedListByType($val['typeId']);
            $arr = [
                'typeId' => $val['typeId'],
                'typeName' => $val['typeName'],
                'videoList' => $typeRecommendedList,
            ];
            if($arr['typeId'] != 49){
                $result[] = $arr;
            }
        }
        return $result;
    }

    /**
     * 获取视频筛选分类列表
     * @return array|false|string
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getVideoFilterTypeList()
    {
        $redis = RedisPool::defer();
        $key = TypeKey::VideoFilterTypeList();
        $data = $redis->get($key);

        if (!$data) {

            $typeList = TypeModel::create()
                ->field(['type_id AS typeId', 'type_name AS typeName', 'type_pid AS typePid', 'type_extend AS typeExtend'])
                ->where(['type_mid' => TypeModel::MID_VIDEO, 'type_status' => TypeModel::STATE_NORMAL])
                ->order('type_pid', 'ASC')
                ->order('type_sort', 'ASC')
                ->all();

            $data = [];

            foreach ($typeList as $typeItem) {
                // 因为sql查询中使用as别名的关系，转数组是转不出来的。需要手动取值。
                //$temp = $typeItem->toRawArray();
                $temp = [
                    'typeId' => $typeItem['typeId'],
                    'typeName' => $typeItem['typeName'],
                    'typePid' => $typeItem['typePid'],
                    'typeExtend' => $typeItem['typeExtend'],
                ];

                if ($temp['typePid'] > 0) {
                    // 二级分类添加到顶级下面
                    $data[$temp['typePid']]['subType'][] = ['typeId' => $temp['typeId'], 'typeName' => $temp['typeName']];
                } else {
                    $temp['typeExtend'] = json_decode($temp['typeExtend'], true);

                    // 筛选出顶级
                    $data[$temp['typeId']] = [
                        'typeId' => $temp['typeId'],
                        'typeName' => $temp['typeName'],
                        'subType' => [],
                        'subArea' => $temp['typeExtend']['area'] ? explode(',', $temp['typeExtend']['area']) : [],
                        'subLang' => $temp['typeExtend']['lang'] ? explode(',', $temp['typeExtend']['lang']) : [],
                        'subYear' => $temp['typeExtend']['year'] ? explode(',', $temp['typeExtend']['year']) : [],
                    ];
                }
            }

            $data = array_values($data);

            $redis->set($key, $data, 60);
        }

        return $data;
    }

    /**
     * 获取成人父级id列表
     * @return array|null
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getAdultPidList()
    {
        return TypeModel::create()
            ->where(['type_pid' => 0, 'type_mid' => TypeModel::MID_ADULT_VIDEO, 'type_status' => TypeModel::STATE_NORMAL])
            ->column('type_id');
    }

    public function getAdultIdList()
    {
        return TypeModel::create()
            ->where(['type_mid' => TypeModel::MID_ADULT_VIDEO, 'type_pid' => [0, '>'], 'type_status' => TypeModel::STATE_NORMAL])
            ->column('type_id');
    }

    /**
     * 获取影视父级id列表
     * @return array|null
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getVideoPidList()
    {
        return TypeModel::create()
            ->where(['type_pid' => 0, 'type_mid' => TypeModel::MID_VIDEO, 'type_status' => TypeModel::STATE_NORMAL])
            ->column('type_id');
    }
}