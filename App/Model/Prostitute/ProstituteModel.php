<?php

namespace App\Model\Prostitute;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\Model\Region\CityModel;
use App\Model\Region\ProvinceModel;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * Class ProstituteModel
 * @package App\Model\Prostitute
 * @property $prostituteId int | 楼凤id
 * @property $prostituteTypeId int | 楼凤分类id
 * @property $title string | 标题
 * @property $content string | 内容
 * @property $type enum | 数据类型 Real 真实的，Ad 广告
 * @property $address string | 图地址
 * @property $contact string | 联系方式
 * @property $provinceId int | 省级id
 * @property $cityId int | 市级id
 * @property $extension string | 扩展参数（JSON字符串）
 * @property $sort int | 排序值，默认0，倒叙排
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ProstituteModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'p_prostitute';

    protected $primaryKey = 'prostituteId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

    const TYPE_REAL = 'Real';
    const TYPE_AD = 'Ad';

    const TYPE_LIST = [
        self::TYPE_REAL,
        self::TYPE_AD,
    ];

    const TYPE_LIST_TEXT = [
        ['key' => self::TYPE_REAL, 'name' => '真实'],
        ['key' => self::TYPE_AD, 'name' => '广告'],
    ];

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function prostitutePictureRelation()
    {
        return $this->hasMany(ProstitutePictureModel::class, function (QueryBuilder $query) {
            $query
                ->orderBy('sort', ' DESC');
            return $query;
        }, 'prostituteId', 'prostituteId');
    }

    public function provinceRelation()
    {
        return $this->hasOne(ProvinceModel::class, null, 'provinceId', 'provinceId');
    }

    public function cityRelation()
    {
        return $this->hasOne(CityModel::class, null, 'cityId', 'cityId');
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['prostituteId']) && $keyword['prostituteId'] > 0 && $where['prostituteId'] = $keyword['prostituteId'];
        isset($keyword['prostituteTypeId']) && $keyword['prostituteTypeId'] > 0 && $where['prostituteTypeId'] = $keyword['prostituteTypeId'];

        // 虽然我很不想在左边加百分号，但是想着总共数据也不多，操作会更方便，还是加上的吧。
        # TODO: 后面改成全文搜索
        isset($keyword['title']) && $keyword['title'] && $where['title'] = ['%' . $keyword['title'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    public function setOrderType($sortType)
    {
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
}