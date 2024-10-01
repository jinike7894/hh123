<?php

namespace App\Model\Prostitute;

use App\Model\BaseModel;

/**
 * Class ProstituteTypeExtensionModel
 * @package App\Model\Prostitute
 * @property $prostituteTypeExtId int | 楼凤分类扩展id
 * @property $prostituteTypeId int | 楼凤分类id
 * @property $fieldKey string | 字段key
 * @property $fieldName string | 字段名
 */
class ProstituteTypeExtensionModel extends BaseModel
{
    protected $tableName = 'p_prostitute_type_extension';

    protected $primaryKey = 'prostituteTypeExtId';
}