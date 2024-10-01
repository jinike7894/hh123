<?php

namespace App\Model\Region;

use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

class ProvinceModel extends BaseModel
{
    protected $tableName = 'region_province';

    protected $primaryKey = 'provinceId';

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function cityRelation()
    {
        return $this->hasMany(CityModel::class, function (QueryBuilder $query) {
            //$query->orderBy('cityId', 'ASC');
            return $query;
        }, 'provinceId', 'provinceId');
    }
}