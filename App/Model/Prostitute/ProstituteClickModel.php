<?php

namespace App\Model\Prostitute;

use App\Model\BaseModel;

/**
 * Class ProstituteClickModel
 * @package App\Model\Prostitute
 * @property $date date | 日期
 * @property $prostituteId int | 楼凤id
 * @property $contact string | 联系方式
 * @property $clickCount int | 点击计数
 */
class ProstituteClickModel extends BaseModel
{
    protected $tableName = 'p_prostitute_click';

    protected $primaryKey = ['date', 'prostituteId'];

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['dateStart'])) {
            $this->where('date', $keyword['dateStart'], '>=');
        }
        if (isset($keyword['dateEnd'])) {
            $this->where('date', $keyword['dateEnd'], '<=');
        }

        return $where;
    }

    public function getSum($keyword)
    {
        $where = $this->parseKeywordToWhere($keyword);

        return $this
            ->field([
                'IFNULL(SUM(clickCount),0) AS clickCount'
            ])
            ->where($where)
            ->get();
    }
}