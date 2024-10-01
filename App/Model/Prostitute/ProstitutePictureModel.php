<?php

namespace App\Model\Prostitute;

use App\Model\BaseModel;

/**
 * Class ProstitutePictureModel
 * @package App\Model\Prostitute
 * @property $prostitutePicId int | 楼凤图片id
 * @property $prostituteId int | 楼凤id
 * @property $fileType enum | 文件类型
 * @property $url string | 链接
 * @property $sort int | 排序值，默认0，倒叙排
 */
class ProstitutePictureModel extends BaseModel
{
    protected $tableName = 'p_prostitute_picture';

    protected $primaryKey = 'prostitutePicId';
}