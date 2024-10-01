<?php

namespace App\Model\Prostitute;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\Model\Navigation\AdModel;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * Class ProstituteTypeModel
 * @package App\Model\Prostitute
 * @property $prostituteTypeId int | 楼凤分类id
 * @property $title string | 标题
 * @property $typeKey string | 分类key
 * @property $relatedAdId string | 关联的广告id
 * @property $sort int | 排序值，默认0，倒叙排
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ProstituteTypeModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'p_prostitute_type';

    protected $primaryKey = 'prostituteTypeId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function prostituteTypeExtensionRelation()
    {
        return $this->hasMany(ProstituteTypeExtensionModel::class, null, 'prostituteTypeId', 'prostituteTypeId');
    }

    public function adRelation()
    {
        return $this->hasOne(AdModel::class, function (QueryBuilder $query) {
            $query->where('status', AdModel::STATE_NORMAL);
            return $query;
        });
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['prostituteTypeId']) && $keyword['prostituteTypeId']) {
            if (is_array($keyword['prostituteTypeId'])) {
                $where['prostituteTypeId'] = [$keyword['prostituteTypeId'], 'IN'];
            } else {
                $where['prostituteTypeId'] = $keyword['prostituteTypeId'];
            }
        }

        // 虽然我很不想在左边加百分号，但是想着总共数据也不多，操作会更方便，还是加上的吧。
        isset($keyword['title']) && $keyword['title'] && $where['title'] = ['%' . $keyword['title'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }
}