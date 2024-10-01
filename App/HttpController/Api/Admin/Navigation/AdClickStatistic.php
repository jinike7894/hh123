<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Merchant\ChannelDownloadModel;
use App\Model\Merchant\ChannelInstallStatisticModel;
use App\Model\Merchant\ChannelModel;
use App\Model\Merchant\MerchantModel;
use App\Model\Navigation\AdClickStatisticModel;
use App\Model\Navigation\AdGroupModel;
use App\Model\Navigation\AdGroupRelationModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\AdTypeModel;
use App\Model\Navigation\LandPageStatisticModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageStatisticModel;
use App\Model\User\UserVipOrderModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use Exception;
use Throwable;

/**
 * Class AdClickStatistic
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-广告点击统计 Admin/Navigation/AdClickStatistic")
 * @ApiGroupDescription("后台广告点击统计。")
 */
class AdClickStatistic extends AdminBase
{
    /**
     * 广告点击统计列表
     * @Api(name="广告点击统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getList")
     * @ApiDescription("广告点击统计列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="adId", alias="广告id", type="int", optional="", min="1", description="广告id")
     * @Param(name="adName", alias="广告名", type="string", optional="", mbLengthMin="1", description="广告名字")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["clickCount_DESC", "clickCount_ASC", "retainedClickCount_DESC", "retainedClickCount_ASC", "totalCost_DESC", "totalCost_ASC", "adId_DESC", "adId_ASC"], description="1.点击数（clickCount）2.留存点击数（retainedClickCount）3.总费用（totalCost）4.广告id（adId）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"date":"2023-11-02","adId":1,"clickCount":"5","retainedClickCount":"3","totalCost":"0.0000","adName":"顶部浮漂","adGroupNameList":"sm-顶部浮动"}],"options":{"dateStart":"2023-11-02","dateEnd":"2023-11-02"},"sum":{"clickCount":"5","retainedClickCount":"3","totalCost":"0.0000"}},"systemTimestamp":1698922554,"systemDateTime":"2023-11-02 18:55:54","msg":"OK"})
     */
    public function getList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['pageName'])) {
                $pageId = PageModel::create()->where(['pageName' => $param['pageName']])->val('pageId');
                $pageId && $keyword['pageId'] = $pageId;
            }
            if (isset($param['adName'])) {
                $adIdList = AdModel::create()->where(['adName' => ['%' . $param['adName'] . '%', 'LIKE']])->column('adId');
                if ($adIdList) {
                    $keyword['adId'] = count($adIdList) > 1 ? $adIdList : current($adIdList);
                }
            }

            if (isset($param['url'])) {
                $adIdList = AdModel::create()->where(['url' => $param['url']])->column('adId');
                if ($adIdList) {
                    $keyword['adId'] = count($adIdList) > 1 ? $adIdList : current($adIdList);
                }
            }
            isset($param['adId']) && $keyword['adId'] = $param['adId'];
            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date',
                'adId',
                'SUM(clickCount) AS clickCount',
                'SUM(retainedClickCount) AS retainedClickCount',
                'SUM(totalCost) AS totalCost',
            ];

            $adClickStatistic = AdClickStatisticModel::create()
                ->group('date, adId')
                ->order('date', 'DESC');

            $sortType = $param['sortType'] ?? '';

            $result = $adClickStatistic
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $result['list'] = AdModel::create()->appendInfo($result['list'], ['adName'], 'adId', 'adId');

            /* 2023-08-30 增加广告所在分组字段 begin */
            if ($result['list']) {
                // 如果筛选广告的情况下需要用结果集来拿广告列表
                if (!isset($adIdList)) {
                    $adIdList = array_column($result['list'], 'adId');
                    $adIdList = array_unique($adIdList);
                }

                $adGroupRelationList = AdGroupRelationModel::create()
                    ->alias('agr')
                    ->field(['agr.adgroupId', 'adGroupName', 'adId'])
                    ->join(AdGroupModel::create()->getTableName() . ' AS ag', 'agr.adGroupId = ag.adGroupId', 'LEFT')
                    ->where(['agr.adId' => [$adIdList, 'IN']])
                    ->all();

                $groupNameRelation = [];
                foreach ($adGroupRelationList as $adGroupRelation) {
                    if (isset($groupNameRelation[$adGroupRelation['adId']])) {
                        $groupNameRelation[$adGroupRelation['adId']] .= ',' . $adGroupRelation['adGroupName'];
                    } else {
                        $groupNameRelation[$adGroupRelation['adId']] = $adGroupRelation['adGroupName'];
                    }
                }

                foreach ($result['list'] as $item) {
                    $item['adGroupNameList'] = $groupNameRelation[$item['adId']] ?? '';
                }
            }

            /* 2023-08-30 增加广告所在分组字段 end */

            if ($param['export']) {
                if (!$result['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['pageName'])) {
                    $fileName = $param['pageName'] . '广告点击数据.xlsx';
                } else {
                    $fileName = '全页面广告点击数据.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['广告id', 'adId'],
                    ['广告名', 'adName'],
                    ['所属广告组', 'adGroupNameList'],
                    ['点击数', 'clickCount'],
                    ['留存点击数', 'retainedClickCount'],
                    ['总费用', 'totalCost'],
                ];
                $this->downloadExcel($headers, $result['list'], $fileName);
            } else {
                // 不是下载的情况才再查总计
                $result['sum'] = AdClickStatisticModel::create()->getSum($keyword);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告点击总计统计列表
     * @Api(name="广告点击总计统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getTotalList")
     * @ApiDescription("广告点击总计统计列表，这个就是包含所有广告的，0次点击要算。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="adTypeId", alias="广告分类id", type="int", optional="", min="1", description="广告分类id")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["clickCount_DESC", "clickCount_ASC", "retainedClickCount_DESC", "retainedClickCount_ASC", "adId_DESC", "adId_ASC"], description="只有在选择了广告分类的情况下才可以选择排序。1.点击数（clickCount）2.留存点击数（retainedClickCount）3.广告id（adId）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":76,"list":[{"adId":1,"adTypeId":2,"adName":"顶部浮漂","clickCount":"5","retainedClickCount":"3","adTypeName":"播放器","adGroupNameList":"sm-顶部浮动"}],"options":{"status":1}},"systemTimestamp":1698922881,"systemDateTime":"2023-11-02 19:01:21","msg":"OK"})
     */
    public function getTotalList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['pageName'])) {
                $pageId = PageModel::create()->where(['pageName' => $param['pageName']])->val('pageId');
                $pageId && $keyword['pageId'] = $pageId;
            }

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'ad.adId',
                'ad.adTypeId',
                'ad.adName',
                'ad.remark',
                'IFNULL(SUM(clickCount),0) AS clickCount',
                'IFNULL(SUM(retainedClickCount),0) AS retainedClickCount',
            ];

            // 因为SQL特殊，这里的条件是join中的on参数
            $whereStr = 'ad.adId = acs.adId';
            foreach ($keyword as $key => $item) {
                $item = addslashes($item);
                switch ($key) {
                    case 'pageId':
                        $whereStr .= " AND acs.pageId = '{$item}'";
                        break;
                    case 'dateStart':
                        $whereStr .= " AND acs.date >= '{$item}'";
                        break;
                    case 'dateEnd':
                        $whereStr .= " AND acs.date <= '{$item}'";
                        break;
                }
            }
            unset($keyword);

            $ad = AdModel::create()
                ->alias('ad')
                ->join(AdClickStatisticModel::create()->getTableName() . ' AS acs', $whereStr, 'LEFT')
                ->group('adId')
                ->order('adTypeId', 'ASC');

            $sortType = $param['sortType'] ?? '';
            if (isset($param['adTypeId']) && $sortType) {
                $sortType = explode('_', $sortType);
                $ad->order(...$sortType);
            } else {
                $ad
                    ->order('clickCount', 'DESC')
                    ->order('adId', 'DESC');
            }

            if (!$param['export']) {
                //$keyword = ['status' => AdModel::STATE_NORMAL];
            }

            isset($param['adTypeId']) && $keyword['adTypeId'] = $param['adTypeId'];
            $result = $ad->getAll($page, $keyword, $pageSize, $field);

            $result['list'] = AdTypeModel::create()->appendInfo($result['list'], ['adTypeName'], 'adTypeId', 'adTypeId');

            /* 2023-08-30 增加广告所在分组字段 begin */
            if ($result['list']) {
                // 如果筛选广告的情况下需要用结果集来拿广告列表
                if (!isset($adIdList)) {
                    $adIdList = array_column($result['list'], 'adId');
                    $adIdList = array_unique($adIdList);
                }

                $adGroupRelationList = AdGroupRelationModel::create()
                    ->alias('agr')
                    ->field(['agr.adgroupId', 'adGroupName', 'adId'])
                    ->join(AdGroupModel::create()->getTableName() . ' AS ag', 'agr.adGroupId = ag.adGroupId', 'LEFT')
                    ->where(['agr.adId' => [$adIdList, 'IN']])
                    ->all();

                $groupNameRelation = [];
                foreach ($adGroupRelationList as $adGroupRelation) {
                    if (isset($groupNameRelation[$adGroupRelation['adId']])) {
                        $groupNameRelation[$adGroupRelation['adId']] .= ',' . $adGroupRelation['adGroupName'];
                    } else {
                        $groupNameRelation[$adGroupRelation['adId']] = $adGroupRelation['adGroupName'];
                    }
                }

                foreach ($result['list'] as $item) {
                    $item['adGroupNameList'] = $groupNameRelation[$item['adId']] ?? '';
                }
            }

            /* 2023-08-30 增加广告所在分组字段 end */

            if ($param['export']) {
                if (!$result['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['pageName'])) {
                    $fileName = $param['pageName'] . '广告点击数据.xlsx';
                } else {
                    $fileName = '全页面广告点击数据.xlsx';
                }
                $headers = [
                    ['广告id', 'adId'],
                    ['广告名-备注', 'adName'],
                    ['广告分类', 'adTypeName'],
                    ['所属广告组', 'adGroupNameList'],
                    ['点击数', 'clickCount'],
                    # TODO：导出这里没处理留存点击数这个字段
                    //['留存点击数', 'retainedClickCount'],
                ];
                $this->exportAdClickStatisticTotalList($headers, $result['list'], $fileName);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告点击分类占比统计列表
     * @Api(name="广告点击分类占比统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getTypeTotalList")
     * @ApiDescription("广告点击分类占比统计列表，这个就是包含所有广告分类的，0次点击要算。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["adTypeId_DESC", "adTypeId_ASC", "clickCount_DESC", "clickCount_ASC"], description="1.广告分类id（adTypeId）2.点击数（clickCount）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":5,"list":[{"adTypeId":5,"adTypeName":"博彩","clickCount":"0","clickCountPercentage":"0.00"},{"adTypeId":4,"adTypeName":"炮台","clickCount":"2","clickCountPercentage":"8.00"},{"adTypeId":3,"adTypeName":"直播","clickCount":"0","clickCountPercentage":"0.00"},{"adTypeId":2,"adTypeName":"播放器","clickCount":"23","clickCountPercentage":"92.00"},{"adTypeId":1,"adTypeName":"默认","clickCount":"0","clickCountPercentage":"0.00"}],"options":[],"sum":{"clickCount":"25"}},"systemTimestamp":1703693257,"systemDateTime":"2023-12-28 00:07:37","msg":"OK"})
     */
    public function getTypeTotalList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['pageName'])) {
                $pageId = PageModel::create()->where(['pageName' => $param['pageName']])->val('pageId');

                if (!$pageId) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $keyword['pageId'] = $pageId;
            }

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            /*
             	select at.adTypeId,at.adTypeName,IFNULL(SUM( clickCount ), 0) AS clickCount
	from nav_ad_type as at
	left join nav_ad as a on a.adTypeId = at.adTypeId
	left join nav_ad_click_statistic as acs on acs.adId = a.adId AND acs.date >= '2023-11-01' AND acs.date <= '2023-12-31'
	GROUP BY adTypeId;

            	select IFNULL(SUM( clickCount ), 0) AS clickCount from nav_ad_click_statistic as acs
	where acs.date >= '2023-11-01' AND acs.date <= '2023-12-31' ;
             */

            $field = [
                'at.adTypeId,at.adTypeName',
                'at.adTypeName',
                'IFNULL(SUM(clickCount), 0) AS clickCount',
            ];

            // 因为SQL特殊，这里的条件是join中的on参数
            $whereStr = 'acs.adId = a.adId';
            foreach ($keyword as $key => $item) {
                $item = addslashes($item);
                switch ($key) {
                    case 'pageId':
                        $whereStr .= " AND acs.pageId = '{$item}'";
                        break;
                    case 'dateStart':
                        $whereStr .= " AND acs.date >= '{$item}'";
                        break;
                    case 'dateEnd':
                        $whereStr .= " AND acs.date <= '{$item}'";
                        break;
                }
            }

            $sortType = $param['sortType'] ?? '';
            $data = AdTypeModel::create()
                ->alias('at')
                ->join(AdModel::create()->getTableName() . ' AS a', 'a.adTypeId = at.adTypeId', 'LEFT')
                ->join(AdClickStatisticModel::create()->getTableName() . ' AS acs', $whereStr, 'LEFT')
                ->group('adTypeId')
                ->setOrderType($sortType)
                ->getAll($page, [], $pageSize, $field);

            $data['sum'] = AdClickStatisticModel::create()
                ->field(['IFNULL(SUM(clickCount), 0) AS clickCount'])
                ->setKeyWord($keyword)
                ->get();

            foreach ($data['list'] as $datum) {
                if ($data['sum']['clickCount'] > 0) {
                    $datum['clickCountPercentage'] = bcmul(bcdiv($datum['clickCount'], $data['sum']['clickCount'], 4), 100, 2);
                } else {
                    $datum['clickCountPercentage'] = '0.00';
                }
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['pageName'])) {
                    $fileName = $param['pageName'] . '广告点击数据.xlsx';
                } else {
                    $fileName = '全页面广告点击数据.xlsx';
                }
                $headers = [
                    ['分类id', 'adTypeId'],
                    ['分类名', 'adTypeName'],
                    ['点击数', 'clickCount'],
                    ['点击占比', 'clickCountPercentage'],
                ];
                $this->exportAdClickStatisticTotalList($headers, $data['list'], $fileName);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告点击分类占比中页面占比列表
     * @Api(name="广告点击分类占比中页面占比列表",path="/Api/Admin/Navigation/AdClickStatistic/getTypeTotalDetail")
     * @ApiDescription("广告点击分类占比中页面占比列表，这个就是包含所有广告分类的，0次点击要算。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["pageId_DESC", "pageId_ASC", "clickCount_DESC", "clickCount_ASC"], description="1.页面id（pageId）2.点击数（clickCount）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":4,"list":[{"pageId":1,"clickCount":"37","pageName":"index.html","clickCountPercentage":"56.92"},{"pageId":2,"clickCount":"19","pageName":"test.html","clickCountPercentage":"29.23"},{"pageId":4,"clickCount":"8","pageName":"dj.html","clickCountPercentage":"12.30"},{"pageId":16,"clickCount":"1","pageName":"v113","clickCountPercentage":"1.53"}],"options":[],"sum":{"clickCount":"65"}},"systemTimestamp":1703763541,"systemDateTime":"2023-12-28 19:39:01","msg":"OK"})
     */
    public function getTypeTotalDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            isset($param['dateStart']) && $keyword['acs.dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['acs.dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            /*
             	select p.pageId, p.pageName, IFNULL(SUM( clickCount ), 0) AS clickCount
from nav_ad_click_statistic as acs
left join nav_ad as a on acs.adId = a.adId
left join nav_page as p on acs.pageId = p.pageId
where a.adTypeId = 2
and acs.date >= '2023-12-01' AND acs.date <= '2023-12-31'
group by acs.pageId
order by clickCount DESC;

select IFNULL(SUM( clickCount ), 0) AS clickCount from nav_ad_click_statistic as acs
left join nav_ad as a on acs.adId = a.adId
where a.adTypeId = 2
and acs.date >= '2023-12-01' AND acs.date <= '2023-12-31' ;

             */

            $field = [
                'p.pageId',
                'p.pageName',
                'IFNULL(SUM(clickCount), 0) AS clickCount',
            ];

            $sortType = $param['sortType'] ?? 'clickCount_DESC';
            $data = AdClickStatisticModel::create()
                ->alias('acs')
                ->join(AdModel::create()->getTableName() . ' AS a', 'acs.adId = a.adId', 'LEFT')
                ->join(PageModel::create()->getTableName() . ' AS p', 'acs.pageId = p.pageId', 'LEFT')
                ->where(['a.adTypeId' => $param['adTypeId']])
                ->group('acs.pageId')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);


            $data['sum'] = AdClickStatisticModel::create()
                ->alias('acs')
                ->field(['IFNULL(SUM(clickCount), 0) AS clickCount'])
                ->join(AdModel::create()->getTableName() . ' AS a', 'acs.adId = a.adId', 'LEFT')
                ->where(['a.adTypeId' => $param['adTypeId']])
                ->setKeyWord($keyword)
                ->get();

            foreach ($data['list'] as $datum) {
                if ($data['sum']['clickCount'] > 0) {
                    $datum['clickCountPercentage'] = bcmul(bcdiv($datum['clickCount'], $data['sum']['clickCount'], 4), 100, 2);
                } else {
                    $datum['clickCountPercentage'] = '0.00';
                }
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['pageName'])) {
                    $fileName = $param['pageName'] . '广告点击数据.xlsx';
                } else {
                    $fileName = '全页面广告点击数据.xlsx';
                }
                $headers = [
                    ['页面id', 'pageId'],
                    ['页面名', 'pageName'],
                    ['点击数', 'clickCount'],
                    ['点击占比', 'clickCountPercentage'],
                ];
                $this->exportAdClickStatisticTotalList($headers, $data['list'], $fileName);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告点击页面统计列表
     * @Api(name="广告点击页面统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getPageStatisticList")
     * @ApiDescription("广告点击页面统计列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["pageId_DESC", "pageId_ASC", "ip_DESC", "ip_ASC", "reducedIp_DESC", "reducedIp_ASC", "ipCost_DESC", "ipCost_ASC", "cost_DESC", "cost_ASC", "clickCost_DESC", "clickCost_ASC", "clickCount_DESC", "clickCount_ASC", "h5ClickCount_DESC", "h5ClickCount_ASC", "appClickCount_DESC", "appClickCount_ASC", "retainedClickCount_DESC", "retainedClickCount_ASC", "clickRate_DESC", "clickRate_ASC", "date_DESC", "date_ASC"], description="pageId 页面id, ip 总ip, reducedIp 扣量后ip, ipCost ip单价, cost 投入成本, clickCost 点击成本, clickCount 点击数, h5ClickCount h5点击数, appClickCount app点击数, retainedClickCount 留存点击数, clickRate 点击比, date 日期")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-15","pageId":1,"ip":2,"reducedIp":0,"pageName":"index.html","ipCost":0,"cost":0,"clickCost":0,"clickCount":"4","h5ClickCount":"1","appClickCount":"3","retainedClickCount":"3","h5ClickRate":"0.5000","paymentUserCount":1,"paymentOrderCount":1,"paymentOrderAmount":"200.00"}],"options":{"ps.dateStart":"2023-11-01","ps.dateEnd":"2023-11-28"},"sum":{"ip":"76","reducedIp":"9","clickCount":"21","h5ClickCount":"12","appClickCount":"9","retainedClickCount":"9","paymentUserCount":1,"paymentOrderCount":1,"paymentOrderAmount":"200.00"}},"systemTimestamp":1702464095,"systemDateTime":"2023-12-13 18:41:35","msg":"OK"})
     */
    public function getPageStatisticList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $sumKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['pageName'])) {
                $pageIdList = PageModel::create()->where(['pageName' => [$param['pageName'] . '%', 'LIKE']])->column('pageId');

                if (!$pageIdList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $sumKeyword['pageId'] = $keyword['ps.pageId'] = $pageIdList;
            }

            isset($param['dateStart']) && $sumKeyword['dateStart'] = $keyword['ps.dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $sumKeyword['dateEnd'] = $keyword['ps.dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'ps.date', // 日期
                'ps.pageId', // 页面id
                'p.pageName', // 页面名
                'ps.ip', // 进站ip
                'ps.reducedIp', // 扣量后ip
                'p.ipCost', // ip单价
                'ps.reducedIp * p.ipCost AS cost', // 投入成本
                'IFNULL((ps.reducedIp * p.ipCost )/ SUM(acs.clickCount),0) AS clickCost', // 点击成本
                'IFNULL(SUM(acs.clickCount),0) AS clickCount', // 点击数
                'IFNULL(SUM(acs.h5ClickCount),0) AS h5ClickCount', // h5点击数
                'IFNULL(SUM(acs.appClickCount),0) AS appClickCount', // app点击数
                'IFNULL(SUM(acs.retainedClickCount),0) AS retainedClickCount', // 留存点击数
                'IFNULL(SUM(acs.h5ClickCount)/ps.ip,0) AS h5ClickRate', // h5点击比
            ];

            $pageStatistic = PageStatisticModel::create();
            $pageStatistic
                ->alias('ps')
                ->group('date, pageId')
                ->order('date', 'DESC');

            $sortType = $param['sortType'] ?? '';

            $data = $pageStatistic
                ->join(PageModel::create()->getTableName() . ' AS p', 'p.pageId = ps.pageId', 'LEFT')
                ->join(AdClickStatisticModel::create()->getTableName() . ' AS acs', 'acs.pageId = ps.pageId AND acs.date = ps.date', 'LEFT')
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            // 2023-12-12 增加渠道对应的支付数据
            # TODO: 这里只写了用户VIP购买订单，如果要加其他的订单要写一个订单聚合统计
            $paymentDataGroup = UserVipOrderModel::create()->getGroupSum($sumKeyword, 'pageId');
            $paymentUserGroup = UserVipOrderModel::create()->getGroupUserCount($sumKeyword, 'pageId');

            foreach ($data['list'] as $datum) {
                $datePageKey = $datum['date'] . '_' . $datum['pageId'];

                // 保留2位小数
                $datum['ipCost'] = floor($datum['ipCost'] * 100) / 100;
                $datum['cost'] = floor($datum['cost'] * 100) / 100;
                $datum['clickCost'] = floor($datum['clickCost'] * 100) / 100;

                // 2023-12-12 增加渠道对应的支付数据
                $datum['paymentUserCount'] = $paymentUserGroup[$datePageKey]['userCount'] ?? 0;
                $datum['paymentOrderCount'] = $paymentDataGroup[$datePageKey]['orderCount'] ?? 0;
                $datum['paymentOrderAmount'] = $paymentDataGroup[$datePageKey]['amount'] ?? 0;
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['pageName'])) {
                    $fileName = $param['pageName'] . '广告点击页面统计列表.xlsx';
                } else {
                    $fileName = '广告点击全页面统计列表.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['页面id', 'pageId'],
                    ['页面名', 'pageName'],
                    ['进站ip', 'ip'],
                    ['扣量后ip', 'reducedIp'],
                    ['ip单价', 'ipCost'],
                    ['投入成本', 'cost'],
                    ['点击成本', 'clickCost'],
                    ['点击数', 'clickCount'],
                    ['h5点击数', 'h5ClickCount'],
                    ['app点击数', 'appClickCount'],
                    ['留存点击数', 'retainedClickCount'],
                    ['h5点击比', 'h5ClickRate'],
                    ['拉单人数', 'paymentUserCount'],
                    ['成功订单数', 'paymentOrderCount'],
                    ['成功订单金额', 'paymentOrderAmount'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                // 不是下载的情况才再查总计
                $data['sum'] = PageStatisticModel::create()->getSum($sumKeyword);
                $adClickSum = AdClickStatisticModel::create()->getSum($sumKeyword);
                $data['sum']['clickCount'] = $adClickSum['clickCount'];
                $data['sum']['h5ClickCount'] = $adClickSum['h5ClickCount'];
                $data['sum']['appClickCount'] = $adClickSum['appClickCount'];
                $data['sum']['retainedClickCount'] = $adClickSum['retainedClickCount'];

                // 2023-12-12 增加渠道对应的支付数据
                $paymentUserSum = UserVipOrderModel::create()->getUserCount($sumKeyword, 'pageId');
                $data['sum']['paymentUserCount'] = $paymentUserSum['userCount'];
                $paymentDataSum = UserVipOrderModel::create()->getSum($sumKeyword, 'pageId');
                $data['sum']['paymentOrderCount'] = $paymentDataSum['orderCount'];
                $data['sum']['paymentOrderAmount'] = $paymentDataSum['amount'];
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 落地页渠道访问统计列表
     * @Api(name="落地页渠道访问统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getLandPageStatisticList")
     * @ApiDescription("落地页渠道访问统计列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"date":"2024-01-05","channelKey":"coo1","ip":3},{"date":"2024-01-05","channelKey":"coo2","ip":1}],"options":{"dateStart":"2024-01-03","dateEnd":"2024-01-06"},"sum":{"ip":"4"}},"systemTimestamp":1704555814,"systemDateTime":"2024-01-06 23:43:34","msg":"OK"})
     */
    public function getLandPageStatisticList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $sumKeyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $default = ['total' => 0, 'list' => [], 'options' => [], 'sum' => []];

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['channelKey'])) {
                $pageIdList = LandPageStatisticModel::create()->where(['channelKey' => [$param['channelKey'] . '%', 'LIKE']])->column('channelKey');

                if (!$pageIdList) {
                    return $this->writeJson(Status::CODE_OK, $default, Status::getReasonPhrase(Status::CODE_OK));
                }

                $sumKeyword['channelKey'] = $keyword['channelKey'] = $pageIdList;

            }



            isset($param['dateStart']) && $sumKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $sumKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date', // 日期
                'channelKey', // 页面id
                'ip', // 进站ip
                'click', //点击统计ip
            ];


            $pageStatistic = LandPageStatisticModel::create();
            $pageStatistic
                ->group('date, channelKey')
                ->order('date', 'DESC');

            $sortType = $param['sortType'] ?? '';

            $data = $pageStatistic
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['channelKey'])) {
                    $fileName = $param['channelKey'] . '落地页渠道访问统计列表.xlsx';
                } else {
                    $fileName = '落地页渠道访问统计列表.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['渠道key', 'channelKey'],
                    ['进站ip', 'ip'],
                    ['点击跳转', 'click'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                // 不是下载的情况才再查总计
                $data['sum'] = LandPageStatisticModel::create()->getSum($sumKeyword);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     *
     * @Api(name="子渠道数据报表",path="/Api/Admin/Navigation/AdClickStatistic/getStatisticSum")
     * @ApiDescription("子渠道数据报表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @ApiSuccess()
     */
    public function getStatisticSum()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            isset($param['dateStart']) &&  $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) &&  $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

//            $paymentDataSum = UserVipOrderModel::create()->getSum($keyword, 'pageId');
//            $h5AccessNum = PageStatisticModel::create()->getSum($keyword);
            $accessSum = LandPageStatisticModel::create()->getSum($keyword);
            $channelInstall = ChannelInstallStatisticModel::create()->getSum($keyword);
            $topDownload = ChannelDownloadModel::create()->getSum($keyword);
            $data = [
//                'android'  => ['newSum' => $newSum['realInstallAndroid']],
//                'h5'       => ['accessSum' => $h5AccessNum['ip']],
//                'recharge' => ['orderSum' => $paymentDataSum['orderCount'], 'amountSum' => $paymentDataSum['amount']],
//                'topDownload' => ['downClick' => $topDownloadNum['downClick']],
                'h5Page' => ['sum' => $accessSum['ip']],
                'dhPage' => ['sum' => $accessSum['dh']],
                'welfarePage' => ['sum' => $accessSum['welfare']],
                'install' => ['sum' => $channelInstall['realInstallAndroid']],
                'topDownload' => ['sum' => $topDownload['downClick']],
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告对应的渠道统计列表
     * @Api(name="广告对应渠道统计列表",path="/Api/Admin/Navigation/AdClickStatistic/getChannelStatisticListByAd")
     * @ApiDescription("广告对应渠道统计列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="adId", alias="广告id", type="int", optional="", min="1", description="广告id")
     * @Param(name="adName", alias="广告名", type="string", optional="", mbLengthMin="1", description="广告名")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", mbLengthMax="32", description="渠道key")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["date_DESC", "date_ASC", "adId_DESC", "adId_ASC", "pageId_DESC", "pageId_ASC", "clickCount_DESC", "clickCount_ASC", "h5ClickCount_DESC", "h5ClickCount_ASC", "appClickCount_DESC", "appClickCount_ASC"], description="1.date 日期 2.adId 广告id 3.pageId 页面id 4.clickCount 点击数 5.h5ClickCount h5点击数 6.appClickCount app点击数")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"date":"2023-11-16","adId":1,"pageId":2,"clickCount":9,"h5ClickCount":7,"appClickCount":2,"datePageKey":"2023-11-16_2","clickCountTotal":"9","clickCountTotalRatio":"1.00","adName":"顶部浮漂","pageName":"test.html","merchantId":1,"channelKey":"test.html","merchantName":"测试商户"},{"date":"2023-11-16","adId":1,"pageId":1,"clickCount":5,"h5ClickCount":4,"appClickCount":1,"datePageKey":"2023-11-16_1","clickCountTotal":"8","clickCountTotalRatio":"0.62","adName":"顶部浮漂","pageName":"index.html","merchantId":1,"channelKey":"index.html","merchantName":"测试商户"},{"date":"2023-11-16","adId":2,"pageId":1,"clickCount":3,"h5ClickCount":0,"appClickCount":3,"datePageKey":"2023-11-16_1","clickCountTotal":"8","clickCountTotalRatio":"0.37","adName":"上门做爱","pageName":"index.html","merchantId":1,"channelKey":"index.html","merchantName":"测试商户"}],"options":{"dateStart":"2023-11-16","dateEnd":"2023-11-16"},"sum":{"clickCount":"17","retainedClickCount":"6","h5ClickCount":"11","appClickCount":"6","totalCost":"0.0000","newClickCount":"11"}},"systemTimestamp":1700137001,"systemDateTime":"2023-11-16 20:16:41","msg":"OK"})
     */
    public function getChannelStatisticListByAd()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $param['export'] = $param['export'] ?? 0;
            if ($param['export']) {
                ini_set('memory_limit', '1024M');
                $pageSize = 300000;
            }

            if (isset($param['adName']) && $param['adName']) {
                $adId = AdModel::create()->where(['adName' => $param['adName']])->val('adId');
                $adId && $keyword['adId'] = $adId;
            }

            isset($param['adId']) && $param['adId'] && $keyword['adId'] = $param['adId'];

            if (isset($param['merchantName']) && $param['merchantName']) {
                $pageIdList = MerchantModel::create()
                    ->alias('m')
                    ->field(['pageId'])
                    ->join(ChannelModel::create()->getTableName() . ' AS c', 'm.merchantId = c.merchantId', 'LEFT')
                    ->join(PageModel::create()->getTableName() . ' AS p', 'c.channelKey = p.pageName', 'LEFT')
                    ->where(['m.merchantName' => $param['merchantName'], 'c.status' => [ChannelModel::STATE_DELETED, '<>']])
                    ->column('pageId');
                $pageIdList && $keyword['pageId'] = $pageIdList;
            }

            if (isset($param['channelKey']) && $param['channelKey']) {
                $pageId = PageModel::create()->where(['pageName' => $param['channelKey']])->val('pageId');
                $pageId && $keyword['pageId'] = $pageId;
            }

            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date',
                'adId',
                'pageId',
                'concat(date,\'_\',pageId) AS datePageKey',
                'clickCount',
                'h5ClickCount',
                'appClickCount',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = AdClickStatisticModel::create()
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            // 补充字段
            if ($data['list']) {
                /* 拿到每个渠道的总点击数据 begin */
                $clickCountTotalList = AdClickStatisticModel::create()
                    ->field(['concat(date,\'_\',pageId) AS datePageKey', 'SUM(clickCount) AS clickCountTotal'])
                    ->setKeyWord($keyword)
                    ->group('datePageKey')
                    ->indexBy('datePageKey');

                foreach ($data['list'] as $datum) {
                    $datum['clickCountTotal'] = $clickCountTotalList[$datum['datePageKey']]['clickCountTotal'] ?? 0;
                    $datum['clickCountTotalRatio'] = $datum['clickCountTotal'] > 0 ? bcdiv($datum['clickCount'], $datum['clickCountTotal'], 2) : 0;
                }

                /* 拿到每个渠道的总点击数据 end */

                $data['list'] = AdModel::create()->appendInfo($data['list'], ['adName'], 'adId', 'adId');
                $data['list'] = PageModel::create()->appendInfo($data['list'], ['pageName'], 'pageId', 'pageId');
                $data['list'] = ChannelModel::create()->appendInfo($data['list'], ['merchantId', 'channelKey'], 'pageName', 'channelKey');
                $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');
            }

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                if (isset($param['adName'])) {
                    $fileName = $param['adName'] . '广告对应的渠道统计列表.xlsx';
                } else {
                    $fileName = '广告对应的渠道统计列表.xlsx';
                }
                $headers = [
                    ['日期', 'date'],
                    ['广告名', 'adName'],
                    ['商户名', 'merchantName'],
                    ['渠道Key', 'channelKey'],
                    ['点击数', 'clickCount'],
                    ['h5点击数', 'h5ClickCount'],
                    ['app点击数', 'appClickCount'],
                    ['渠道点击总数', 'clickCountTotal'],
                    ['点击占比', 'clickCountTotalRatio'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                // 不是下载的情况才再查总计
                $data['sum'] = AdClickStatisticModel::create()->getSum($keyword);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}