<?php

namespace App\Model\Navigation;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class AdModel
 * @package App\Model\Navigation
 * @property $adId int | id
 * @property $adName string | 广告名
 * @property $fileType enum | 文件类型
 * @property $imageUrl string | 图片地址
 * @property $url string | 跳转链接
 * @property $extension string | 扩展参数（JSON字符串）
 * @property $merchantId int | 商户id
 * @property $cost float | 单次点击价格
 * @property $status int | 状态
 * @property $isTest int | 是否为测试
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class AdModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'nav_ad';

    protected $primaryKey = 'adId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

    const TEST_YES = 1;
    const TEST_NO = 0;

    /**
     * 关联预查询
     * 通过 ->with() 方法调用
     * https://www.easyswoole.com/Components/Orm/Associat/preWithQuery.html
     */
    public function adGroup()
    {
        return $this->hasMany(AdGroupRelationModel::class, null, 'adId', 'adId');
    }

    public function getAdAll($page = 1, $keyword = [], $pageSize = 20, $field = ['*'])
    {
        $where = $this->parseKeywordToWhere($keyword);

        // 如果筛选了分组则需要连关联表，否则只是单表查询也不用担心会有连表去重的问题，因为连表必有参数。
        if (isset($where['adGroupId'])) {
            $this->join(AdGroupRelationModel::create()->getTableName() . ' AS agr', 'ad.adId = agr.adId', 'LEFT');
        }

        $list = $this
            ->alias('ad')
            ->limit($pageSize * ($page - 1), $pageSize)
            ->withTotalCount()
            ->field($field)
            ->where($where)
            ->all();

        $total = $this->lastQueryResult()->getTotalCount();

        foreach ($list as &$item) {
            $item = $item->toRawArray();
            $item['extension'] = json_decode($item['extension'], true);
        }

        return ['total' => $total, 'list' => $list, 'options' => $keyword];
        //return ['total' => $total, 'list' => $list, 'options' => $keyword, 'query' => $this->lastQuery()->getLastQuery()];
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['adId']) && $keyword['adId'] && $where['adId'] = is_array($keyword['adId']) ? [$keyword['adId'], 'IN'] : $keyword['adId'];
        isset($keyword['adGroupId']) && $keyword['adGroupId'] > 0 && $where['adGroupId'] = $keyword['adGroupId'];
        isset($keyword['adTypeId']) && $keyword['adTypeId'] > 0 && $where['adTypeId'] = $keyword['adTypeId'];
        isset($keyword['merchantId']) && $keyword['merchantId'] > 0 && $where['merchantId'] = $keyword['merchantId'];

        // 虽然我很不想在左边加百分号，但是想着总共数据也不多，操作会更方便，还是加上的吧。
        isset($keyword['adName']) && $keyword['adName'] && $where['adName'] = ['%' . $keyword['adName'] . '%', 'LIKE'];
        isset($keyword['url']) && $keyword['url'] && $where['url'] = ['%' . $keyword['url'] . '%', 'LIKE'];
        isset($keyword['remark']) && $keyword['remark'] && $where['remark'] = ['%' . $keyword['remark'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

    /**
     * 通过广告组id列表获取所有关联广告
     * @param $groupIdList
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getAllByGroup($groupIdList)
    {

        if (!$groupIdList) {
            return [];
        }

        /*select a.adId,agr.adGroupId,agr.sort,a.adName,a.fileType,a.imageUrl,a.extension from nav_ad as a
left join nav_ad_group_relation as agr on a.adId = agr.adId
where adGroupId in (1,2,3,4,5,6,7,8,9)
and a.status = 1
order by adGroupId ASC, sort ASC, adId ASC;*/

        return $this->alias('a')
            //->field(['a.adId', 'agr.adGroupId', 'agr.sort', 'a.adName', 'a.fileType', 'a.imageUrl', 'a.extension'])
            // 2023-07-27 把url跳转链接直接返回给前端，使点击后直接跳转避免被拦截。
            ->field(['a.adId', 'agr.adGroupId', 'agr.sort', 'a.adName', 'a.fileType', 'a.imageUrl', 'a.imageUrl2', 'a.url', 'a.extension'])
            ->join(AdGroupRelationModel::create()->getTableName() . ' AS agr', 'a.adId = agr.adId', 'LEFT')
            ->where([
                'adGroupId' => [$groupIdList, 'IN'],
                'a.status' => AdModel::STATE_NORMAL,
            ])
            ->order('adGroupId', 'ASC')
            ->order('sort', 'ASC')
            ->order('adId', 'ASC')
            ->all();
    }
}