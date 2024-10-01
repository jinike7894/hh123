<?php

namespace App\Model\Navigation;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class PageTemplateZoneRelationModel
 * @package App\Model\Navigation
 * @property $pageTemplateZoneRelationId int |
 * @property $pageTemplateId int | 页面模板id
 * @property $zoneId int | 广告位id
 * @property $adGroupId int | 关联的广告组id
 * @property $status int | 状态
 * @property $sort int | 多个广告组间的排序，单广告组则不需要。正序排列。
 */
class PageTemplateZoneRelationModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'nav_page_template_zone_relation';

    protected $primaryKey = 'pageTemplateZoneRelationId';

    public function getTemplateZone($templateId, $status = null)
    {
        $status !== null && $this->where(['p.status' => $status]);

        /* SELECT  z.zoneId, z.zoneName, z.zoneKey, p.status, p.sort, ag.adGroupId, ag.adGroupName, ag.adGroupAlias, ag.adGroupKey FROM nav_page_template_zone_relation AS `p` LEFT JOIN nav_zone AS z on p.zoneId = z.zoneId LEFT JOIN nav_ad_group AS ag on p.adGroupId = ag.adGroupId WHERE  `p`.`pageTemplateId` = '1'  ORDER BY z.zoneId ASC, p.sort ASC, p.adGroupId ASC */
        $data = $this->alias('p')
            ->field([
                'p.pageTemplateZoneRelationId',
                'z.zoneId',
                'z.zoneName',
                'z.zoneKey',
                'p.status',
                'p.sort',
                'ag.adGroupId',
                'ag.adGroupName',
                'ag.adGroupAlias',
                'ag.adGroupKey',
            ])
            ->join(ZoneModel::create()->getTableName() . ' AS z', 'p.zoneId = z.zoneId', 'LEFT')
            ->join(AdGroupModel::create()->getTableName() . ' AS ag', 'p.adGroupId = ag.adGroupId', 'LEFT')
            ->where([
                'p.pageTemplateId' => $templateId,
            ])
            ->order('z.zoneId', 'ASC')
            ->order('p.sort', 'ASC')
            ->order('p.adGroupId', 'ASC')
            ->all();

//        $sql = $this->lastQuery()->getLastQuery();
//        var_dump($sql);
        return $data;
    }

}