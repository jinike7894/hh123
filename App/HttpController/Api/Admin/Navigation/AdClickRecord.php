<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Navigation\AdClickRecordModel;
use App\Model\Navigation\AdClickStatisticModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\PageModel;
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
use Ritaswc\ZxIPAddress\IPv4Tool;
use Throwable;

/**
 * Class AdClickRecord
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-广告点击记录 Admin/Navigation/AdClickRecord")
 * @ApiGroupDescription("后台广告点击记录。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class AdClickRecord extends AdminBase
{
    /**
     * 广告点击记录列表
     * @Api(name="广告点击记录列表",path="/Api/Admin/Navigation/AdClickRecord/getList")
     * @ApiDescription("广告点击记录列表
     * 字段说明：date 日期，screen 屏幕宽高，ip ipv4，ipAddress ip地址，clickCount 点击次数，adName 广告名，firstTime 首次点击时间，latestTime 最后点击时间")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="adId", alias="广告id", type="int", optional="", min="1", description="广告id")
     * @Param(name="adName", alias="广告名", type="string", optional="", mbLengthMin="1", description="广告名字")
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="screen", alias="屏幕宽高", type="string", optional="", mbLengthMin="1", description="屏幕宽高 格式：390x844")
     * @Param(name="ip", alias="ip地址", type="string", optional="", mbLengthMin="1", description="ip地址 格式：127.0.0.1")
     * @Param(name="clickCount", alias="最少点击数", type="int", optional="", min="1", integer="", description="最少点击数")
     * @Param(name="deviceId", alias="设备id", type="string", optional="", mbLengthMin="1", description="设备id，原生用真实的，h5用https://github.com/fingerprintjs/fingerprintjs")
     * @ApiSuccess({"code":200,"result":{"total":5,"list":[{"pageId":1,"date":"2023-11-14","deviceId":"aaff17e733ae21df8089409bb723dfc6","adId":1,"screen":"390x844","ip":"15.25.61.3","clickCount":4,"firstTime":"2023-11-14 18:46:24","latestTime":"2023-11-14 18:46:36","adName":"顶部浮漂","pageName":"index.html","ipAddress":"美国 惠普HP"},{"pageId":1,"date":"2023-11-13","deviceId":"aaff17e733ae21df8089409bb723dfc6","adId":1,"screen":"390x844","ip":"172.18.0.1","clickCount":5,"firstTime":"2023-11-13 16:13:47","latestTime":"2023-11-13 16:49:19","adName":"顶部浮漂","pageName":"index.html","ipAddress":"局域网 对方和您在同一内部网"}],"options":[]},"systemTimestamp":1699959594,"systemDateTime":"2023-11-14 18:59:54","msg":"OK"})
     */
    public function getList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

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

            isset($param['adId']) && $keyword['adId'] = $param['adId'];
            isset($param['dateStart']) && $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            if (isset($param['screen'])) {
                $screen = explode('x', $param['screen']);
                if (count($screen) == 2) {
                    $keyword['screen'] = $param['screen'];
                }
            }

            isset($param['ip']) && $keyword['ipLong'] = ip2long($param['ip']);
            isset($param['clickCount']) && $keyword['clickCount'] = $param['clickCount'];
            isset($param['deviceId']) && $keyword['deviceId'] = $param['deviceId'];

            $field = [
                'pageId',
                'date',
                'deviceId',
                'adId',
                'screen',
                'ip',
                'clickCount',
                'firstTime',
                'latestTime',
            ];

            $adClickStatistic = AdClickRecordModel::create()
                ->order('date', 'DESC')
                ->order('clickCount', 'DESC')
                ->order('adId', 'DESC');

            $result = $adClickStatistic->getAll($page, $keyword, $pageSize, $field);

            $result['list'] = AdModel::create()->appendInfo($result['list'], ['adName'], 'adId', 'adId');
            $result['list'] = PageModel::create()->appendInfo($result['list'], ['pageName'], 'pageId', 'pageId');

            foreach ($result['list'] as &$item) {
                $address = IPv4Tool::query($item['ip']);
                $item['ipAddress'] = $address['disp'] ?? '';
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告点击走势图
     * @Api(name="广告点击走势图",path="/Api/Admin/Navigation/AdClickRecord/getTrend")
     * @ApiDescription("广告点击走势图")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"yesterdayData":["4643","7070","5782"],"todayData":["6851","10996","9253"]},"systemTimestamp":1716020501,"systemDateTime":"2024-05-18 16:21:41","msg":"OK"})
     */
    public function getTrend()
    {
        try {
            $today = date('Y-m-d');
            $todayClicks = AdClickRecordModel::create()
                    ->field([
                        'DATE_FORMAT(firstTime, "%H:00") as click_time',
                        'IFNULL(SUM(clickCount),0) AS clickCount',
                    ])
                    ->where('date', $today)
                    ->group('click_time')
                    ->all();

            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $yesterdayClicks = AdClickRecordModel::create()
                ->field([
                    'DATE_FORMAT(firstTime, "%H:00") as click_time',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where('date', $yesterday)
                ->group('click_time')
                ->all();



            $lastWeekEnd = date('Y-m-d', strtotime('last Sunday'));
            $lastWeekStart = date('Y-m-d', strtotime($lastWeekEnd . ' -6 days'));

            $lastWeekClicks = AdClickStatisticModel::create()
                ->field([
                    'date as click_time',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where('date', $lastWeekStart, '>=')
                ->where('date', $lastWeekEnd, '<=')
                ->group('click_time')
                ->all();

            $thisWeekStart = date('Y-m-d', strtotime($lastWeekEnd . ' +1 day'));

            $thisWeekClicks = AdClickStatisticModel::create()
                ->field([
                    'date as click_time',
                    'IFNULL(SUM(clickCount),0) AS clickCount',
                ])
                ->where('date', $thisWeekStart, '>=')
                ->where('date', $today, '<=')
                ->group('click_time')
                ->all();


            $result = [
                'yesterdayClicks' => $yesterdayClicks,
                'todayClicks' => $todayClicks,
                'thisWeekClicks' => $thisWeekClicks,
                'lastWeekClicks' => $lastWeekClicks,
            ];
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
}