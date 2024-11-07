<?php

namespace App\HttpController\Api\Home;

use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\Model\Common\ConfigModel;
use App\Model\Navigation\AdModel;
use App\Model\Navigation\AdGroupRelationModel;
use App\Model\Navigation\PageModel;
use App\Model\Live\LiveTongChengModel;
use App\Model\Live\LiveQingquModel;
use App\Model\Live\LiveNewModel;
use App\Model\Navigation\PageTemplateModel;
use App\Model\Merchant\ChannelNewModel;
use App\Model\GameColumn\GameColumn;
use App\RedisKey\Navigation\TemplateKey;
use App\Service\Merchant\AutoChannelService;
use App\Service\Navigation\AdService;
use App\Service\Navigation\PageService;
use App\Service\Navigation\PageViewService;
use App\Utility\Func;
use App\Utility\LogHandler;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Core;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\RedisPool\RedisPool;
use App\Service\Oss\AwsOssService;
use App\Enum\RedisDb;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * Class Index
 * @package App\HttpController\Api\Home
 * @ApiGroup(groupName="首页 Home/Index")
 * @ApiGroupDescription("首页相关的操作")
 */
class Index extends ApiBase
{
    /**
     * 首页数据
     * @Api(name="首页数据",path="/Api/Home/Index/index")
     * @ApiDescription("首页数据")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="dataVersion", alias="数据版本", type="int", defaultValue="1", min="1", integer="", description="数据版本, 1.数据为数组 2.数据为对象。")
     * @ApiSuccess({"code":200,"result":{"page":{"pageId":1,"pageName":"index.html","pageTemplateId":1,"code":"<script>console.log('index')</script>"},"template":{"pageTemplateId":1,"pageTemplateKey":"default"},"config":{"WebsiteTitle":"ES导航","WebsiteKeywords":"ES导航关键字","WebsiteDescription":"ES导航描述","WebsiteContact":"广告联系TG:XX","CDN":"","Favicon":"","WebsiteCustomerService":"","WebsiteContactGroup":"","AwsS3Host":"","WebsiteStatisticEnabled":0,"WebsiteStatisticConfig":""},"templateData":[{"zoneId":1,"adGroup":[{"adGroupId":1,"sort":0,"adGroupName":"sm-顶部浮动","adGroupAlias":"","adGroupKey":"topFloat","adList":[{"adId":1,"adName":"顶部浮漂","fileType":"up","imageUrl":"/Init/Zone/1/1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":1}]}],"zoneName":"顶部浮动","zoneKey":"topFloat"},{"zoneId":2,"adGroup":[{"adGroupId":2,"sort":0,"adGroupName":"sm-横幅","adGroupAlias":"","adGroupKey":"banner","adList":[{"adId":2,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/2/2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":2},{"adId":3,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/2/3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":2}]}],"zoneName":"横幅","zoneKey":"banner"},{"zoneId":3,"adGroup":[{"adGroupId":3,"sort":10,"adGroupName":"sm-tab热门","adGroupAlias":"热门","adGroupKey":"tabHot","adList":[{"adId":4,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":5,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/3/3_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":6,"adName":"珊瑚直播","fileType":"up","imageUrl":"/Init/Zone/3/3_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":7,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":8,"adName":"情欲直播","fileType":"up","imageUrl":"/Init/Zone/3/3_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":9,"adName":"成人抖音","fileType":"up","imageUrl":"/Init/Zone/3/3_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":10,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":11,"adName":"妹团直播","fileType":"up","imageUrl":"/Init/Zone/3/3_8.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3}]},{"adGroupId":4,"sort":20,"adGroupName":"sm-tab视频","adGroupAlias":"视频","adGroupKey":"tabVideo","adList":[{"adId":12,"adName":"西门视频","fileType":"up","imageUrl":"/Init/Zone/3/4_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":13,"adName":"快色视频","fileType":"up","imageUrl":"/Init/Zone/3/4_2.png","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":14,"adName":"樱桃视频","fileType":"up","imageUrl":"/Init/Zone/3/4_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":15,"adName":"pilipili","fileType":"up","imageUrl":"/Init/Zone/3/4_4.gif","url":"https://www.bilibili.com","extension":{"35":{"description":"动漫涩情应有尽有","downloads":"下载量：500万"}},"adGroupId":4},{"adId":16,"adName":"AV破解版","fileType":"up","imageUrl":"/Init/Zone/3/4_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":17,"adName":"抖阴漫画","fileType":"up","imageUrl":"/Init/Zone/3/4_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":18,"adName":"免费AV动漫","fileType":"up","imageUrl":"/Init/Zone/3/4_7.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4}]},{"adGroupId":5,"sort":30,"adGroupName":"sm-tab直播","adGroupAlias":"直播","adGroupKey":"tabLive","adList":[{"adId":19,"adName":"伊人直播","fileType":"up","imageUrl":"/Init/Zone/3/5_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":5},{"adId":20,"adName":"青草直播","fileType":"up","imageUrl":"/Init/Zone/3/5_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":5}]},{"adGroupId":6,"sort":40,"adGroupName":"sm-tab游戏","adGroupAlias":"游戏","adGroupKey":"tabGame","adList":[{"adId":21,"adName":"多米体育","fileType":"up","imageUrl":"/Init/Zone/3/6_1.png","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":22,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/3/6_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":23,"adName":"澳门新葡京","fileType":"up","imageUrl":"/Init/Zone/3/6_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":24,"adName":"官方威尼斯人","fileType":"up","imageUrl":"/Init/Zone/3/6_4.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":25,"adName":"太阳城集团","fileType":"up","imageUrl":"/Init/Zone/3/6_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":26,"adName":"永利娱乐城","fileType":"up","imageUrl":"/Init/Zone/3/6_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6}]}],"zoneName":"标签页","zoneKey":"tab"},{"zoneId":4,"adGroup":[{"adGroupId":7,"sort":0,"adGroupName":"sm-推荐","adGroupAlias":"下载推荐","adGroupKey":"recommend","adList":[{"adId":27,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"4257004"}},"adGroupId":7},{"adId":28,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/4/7_1.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"831204"}},"adGroupId":7},{"adId":29,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"2196780"}},"adGroupId":7},{"adId":30,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"1834574"}},"adGroupId":7},{"adId":31,"adName":"免费X站","fileType":"up","imageUrl":"/Init/Zone/4/7_2.png","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"8751654"}},"adGroupId":7},{"adId":32,"adName":"91免费版","fileType":"up","imageUrl":"/Init/Zone/4/7_3.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"722524"}},"adGroupId":7}]}],"zoneName":"推荐","zoneKey":"recommend"},{"zoneId":5,"adGroup":[{"adGroupId":8,"sort":0,"adGroupName":"sm-约会","adGroupAlias":"","adGroupKey":"date","adList":[{"adId":33,"adName":"约会1","fileType":"up","imageUrl":"/Init/Zone/5/1.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"在校学生","tag2":"寻求刺激","tag3":"潮吹喷水","age":"19","height":"163cm","cup":"B杯罩","district":"上海"}},"adGroupId":8},{"adId":34,"adName":"约会2","fileType":"up","imageUrl":"/Init/Zone/5/2.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"清纯妹妹","tag2":"可盐可甜","tag3":"乖巧可爱","age":"24","height":"168cm","cup":"C杯罩","district":"成都"}},"adGroupId":8},{"adId":35,"adName":"约会3","fileType":"up","imageUrl":"/Init/Zone/5/3.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"公司秘书","tag2":"野性放荡","tag3":"反差美女","age":"24","height":"172cm","cup":"D杯罩","district":"北京"}},"adGroupId":8},{"adId":36,"adName":"约会4","fileType":"up","imageUrl":"/Init/Zone/5/4.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"超级辣妈","tag2":"老公不在","tag3":"家里可约","age":"25","height":"163cm","cup":"C杯罩","district":"深圳"}},"adGroupId":8},{"adId":37,"adName":"约会5","fileType":"up","imageUrl":"/Init/Zone/5/5.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"极品御姐","tag2":"粉嫩无毛","tag3":"酒店可约","age":"27","height":"174cm","cup":"F杯罩","district":"全国"}},"adGroupId":8},{"adId":38,"adName":"约会6","fileType":"up","imageUrl":"/Init/Zone/5/6.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"兼职约啪","tag2":"在校学生","tag3":"交友线下","age":"19","height":"167cm","cup":"B杯罩","district":"成都"}},"adGroupId":8},{"adId":39,"adName":"约会7","fileType":"up","imageUrl":"/Init/Zone/5/7.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"姐妹花❀","tag2":"性感双飞","tag3":"随时可约","age":"23","height":"170cm","cup":"C杯罩","district":"全国"}},"adGroupId":8}]}],"zoneName":"约会","zoneKey":"date"},{"zoneId":6,"adGroup":[{"adGroupId":9,"sort":0,"adGroupName":"sm-底部浮动","adGroupAlias":"","adGroupKey":"bottomFloat","adList":[{"adId":40,"adName":"底部浮漂","fileType":"up","imageUrl":"/Init/Zone/6/1.gif","url":"https://www.bilibili.com","extension":{"9":{"title":"上门做爱","description":"打造最高端的情色盛宴"},"36":{"title":"上门做爱","description":"打造最高端的情色盛宴"},"37":{"title":"上门做爱","description":"打造最高端的情色盛宴"}},"adGroupId":9}]}],"zoneName":"底部浮动","zoneKey":"bottomFloat"}]},"systemTimestamp":1698751286,"systemDateTime":"2023-10-31 19:21:26","msg":"success"})
     */
    public function index()
    {
        $param = $this->request()->getRequestParam();
        try {
            $page = null;

            if (isset($param['pageName']) && $param['pageName']) {
                $page = PageModel::create()->getByCache($param['pageName']);
            }

            $dataVersion = (int)($param['dataVersion'] ?? 1);

            if (!$page) {
                $page = PageModel::create()->getByCache('index.html');
            }

            if (!$page) {
                throw new Exception('无效的页面参数', Status::CODE_BAD_REQUEST);
            }

            $result = PageService::getInstance()->getViewData($page, $dataVersion);
             //查询aws的桶
             $AwsS3Bucket=ConfigModel::create()->where(["cfgKey"=>"AwsS3Bucket"])->get();
             $result["config"]["AwsS3Bucket"]=$AwsS3Bucket["cfgValue"];
            // 因为要求用纯静态html，所以pv数据无法实时显示。除非单独用其他接口调用，但那样就本末倒置了。所以现在不再返回统计数据。
            // $statistic = PageViewService::getInstance()->getStatistic($page->pageId);

            /*$result = [
                'page' => $page->visible(['pageId', 'pageName', 'pageTemplateId', 'code']),
                'template' => $template->visible(['pageTemplateId', 'pageTemplateKey']),
                'config' => $config,
                'templateData' => $templateData,
                'statistic' => ['pv' => $statistic['pv'] + 1],
            ];*/
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    
    public function getAdHost(){
        $config=ConfigModel::create()->where("cfgKey","AwsS3HostAd")->get();

        return $this->writeJson(Status::CODE_OK, ["host"=>$config["cfgValue"]], Status::getReasonPhrase(Status::CODE_OK));
    }
    //文字广告
    public function fontAd(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:Font");
            // if($AdFontData){
            //     return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            // }
            //查询文字广告内容
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(AdModel::create()->getTableName() . ' AS ad', 'ad.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>76,"ad.status"=>1])
            ->order("relation.sort","desc")
            ->all();
          
           
            //存入缓存
            // $redis->set("Ad:Font",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    //赚钱广告
    public function moneyAd(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:Money");
            // if($AdFontData){
            //     return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            // }
            //查询文字广告内容
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(AdModel::create()->getTableName() . ' AS ad', 'ad.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>77,"ad.status"=>1])
            ->order("relation.sort","desc")
            ->all();
            if($res){
                $AwsS3Bucket=ConfigModel::create()->where(["cfgKey"=>"AwsS3Host"])->get();
                foreach($res as $k=>$v){
                    $res[$k]["imageUrl"]=$AwsS3Bucket["cfgValue"]. $v["imageUrl"];
                }
            }
            //存入缓存
            // $redis->set("Ad:Money",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
     //同城交友
     public function jiaoyouAd(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:jiaoyou");
            // if($AdFontData){
            //     return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            // }
            //查询同城交友广告
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(liveTongChengModel::create()->getTableName() . ' AS tongcheng', 'tongcheng.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>78,"tongcheng.status"=>1])
            ->order("tongcheng.sort","desc")
            ->all();
            if($res){
                $AwsS3Bucket=ConfigModel::create()->where(["cfgKey"=>"AwsS3Host"])->get();
                foreach($res as $k=>$v){
                    $res[$k]["cover"]=$AwsS3Bucket["cfgValue"]. $v["cover"];
                }
            }
            //存入缓存
            // $redis->set("Ad:jiaoyou",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取直播
    public function  liveAd(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:live");
            // if($AdFontData){
            //     return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            // }
            //查询直播广告
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(LiveNewModel::create()->getTableName() . ' AS live', 'live.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>79,"live.status"=>1])
            ->order("live.sort","desc")
            ->all();
            if($res){
                $AwsS3Bucket=ConfigModel::create()->where(["cfgKey"=>"AwsS3Host"])->get();
                foreach($res as $k=>$v){
                    $res[$k]["cover"]=$AwsS3Bucket["cfgValue"]. $v["cover"];
                }
            }
            //存入缓存
            // $redis->set("Ad:live",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取情趣商城
    public function  qingquAd(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:qingqu");
            // if($AdFontData){
            //     return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            // }
            //查询直播广告
            $adGroupRelationModel=AdGroupRelationModel::create()->alias('relation');
            $res=$adGroupRelationModel
            ->join(LiveQingquModel::create()->getTableName() . ' AS qingqu', 'qingqu.adId = relation.adId', 'LEFT')
            ->where(["relation.adGroupId"=>80,"qingqu.status"=>1])
            ->order("qingqu.sort","desc")
            ->all();
            if($res){
                $AwsS3Bucket=ConfigModel::create()->where(["cfgKey"=>"AwsS3Host"])->get();
                foreach($res as $k=>$v){
                    $res[$k]["cover"]=$AwsS3Bucket["cfgValue"]. $v["cover"];
                }
            }
            //存入缓存
            // $redis->set("Ad:qingqu",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取游戏栏目
    public function  gameColumn(){
        try {
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
            $AdFontData=$redis->get("Ad:game_column");
            if($AdFontData){
                return $this->writeJson(Status::CODE_OK, $AdFontData, Status::getReasonPhrase(Status::CODE_OK));
            }
            //查询直播广告
            $adGroupRelationModel=GameColumn::create();
            $res=$adGroupRelationModel
            // ->join(LiveQingquModel::create()->getTableName() . ' AS qingqu', 'qingqu.adId = relation.adId', 'LEFT')
            // ->where(["relation.adGroupId"=>80,"qingqu.status"=>1])
            // ->order("qingqu.sort","desc")
            ->where(["id"=>1])
            ->get();
            //存入缓存
            $redis->set("Ad:game_column",$res,60*5);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    /**
     * 获取配置
     * @Api(name="获取配置",path="/Api/Home/Index/getConfig")
     * @ApiDescription("获取配置")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="dataVersion", alias="数据版本", type="int", defaultValue="1", min="1", integer="", description="数据版本, 1.数据为数组 2.数据为对象。")
     * @ApiSuccess()
     */
    public function getConfig()
    {
        $param = $this->request()->getRequestParam();
        try {
            $page = null;

            if (isset($param['pageName']) && $param['pageName']) {
                $page = PageModel::create()->getByCache($param['pageName']);
            }

            $dataVersion = (int)($param['dataVersion'] ?? 1);

            if (!$page) {
                list($page,) = AutoChannelService::getInstance()->autoCreatePageAndChannel($param['pageName']);
            }

            if (!$page) {
                throw new Exception('无效的页面参数', Status::CODE_BAD_REQUEST);
            }

            $redis = RedisPool::defer();
            $key = TemplateKey::pageConfigCache($page['pageId']);
            $result = $redis->get($key);
            if(!$result){
                $result = PageService::getInstance()->getViewData($page, $dataVersion);
                $redis->set($key, $result, 1200);
            }

            PageViewService::getInstance()->recordView([
                'page' => $page,
                'ip' => $this->clientRealIP(),
                'date' => date('Y-m-d'),
                'ipState' => 1,
            ]);
            // 因为要求用纯静态html，所以pv数据无法实时显示。除非单独用其他接口调用，但那样就本末倒置了。所以现在不再返回统计数据。
            // $statistic = PageViewService::getInstance()->getStatistic($page->pageId);

            /*$result = [
                'page' => $page->visible(['pageId', 'pageName', 'pageTemplateId', 'code']),
                'template' => $template->visible(['pageTemplateId', 'pageTemplateKey']),
                'config' => $config,
                'templateData' => $templateData,
                'statistic' => ['pv' => $statistic['pv'] + 1],
            ];*/
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 统计代码
     * @Api(name="统计代码",path="/Api/Home/Index/statistics")
     * @ApiDescription("统计代码")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @ApiSuccess({"code":200,"result":{"page":{"pageId":1,"pageName":"index.html","pageTemplateId":1,"code":"<script>console.log('index')</script>"},"template":{"pageTemplateId":1,"pageTemplateKey":"default"},"config":{"WebsiteTitle":"ES导航","WebsiteKeywords":"ES导航关键字","WebsiteDescription":"ES导航描述","WebsiteContact":"广告联系TG:XX","CDN":"","Favicon":"","WebsiteCustomerService":"","WebsiteContactGroup":"","AwsS3Host":"","WebsiteStatisticEnabled":0,"WebsiteStatisticConfig":""},"templateData":[{"zoneId":1,"adGroup":[{"adGroupId":1,"sort":0,"adGroupName":"sm-顶部浮动","adGroupAlias":"","adGroupKey":"topFloat","adList":[{"adId":1,"adName":"顶部浮漂","fileType":"up","imageUrl":"/Init/Zone/1/1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":1}]}],"zoneName":"顶部浮动","zoneKey":"topFloat"},{"zoneId":2,"adGroup":[{"adGroupId":2,"sort":0,"adGroupName":"sm-横幅","adGroupAlias":"","adGroupKey":"banner","adList":[{"adId":2,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/2/2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":2},{"adId":3,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/2/3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":2}]}],"zoneName":"横幅","zoneKey":"banner"},{"zoneId":3,"adGroup":[{"adGroupId":3,"sort":10,"adGroupName":"sm-tab热门","adGroupAlias":"热门","adGroupKey":"tabHot","adList":[{"adId":4,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":5,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/3/3_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":6,"adName":"珊瑚直播","fileType":"up","imageUrl":"/Init/Zone/3/3_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":7,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":8,"adName":"情欲直播","fileType":"up","imageUrl":"/Init/Zone/3/3_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":9,"adName":"成人抖音","fileType":"up","imageUrl":"/Init/Zone/3/3_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":10,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3},{"adId":11,"adName":"妹团直播","fileType":"up","imageUrl":"/Init/Zone/3/3_8.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":3}]},{"adGroupId":4,"sort":20,"adGroupName":"sm-tab视频","adGroupAlias":"视频","adGroupKey":"tabVideo","adList":[{"adId":12,"adName":"西门视频","fileType":"up","imageUrl":"/Init/Zone/3/4_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":13,"adName":"快色视频","fileType":"up","imageUrl":"/Init/Zone/3/4_2.png","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":14,"adName":"樱桃视频","fileType":"up","imageUrl":"/Init/Zone/3/4_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":15,"adName":"pilipili","fileType":"up","imageUrl":"/Init/Zone/3/4_4.gif","url":"https://www.bilibili.com","extension":{"35":{"description":"动漫涩情应有尽有","downloads":"下载量：500万"}},"adGroupId":4},{"adId":16,"adName":"AV破解版","fileType":"up","imageUrl":"/Init/Zone/3/4_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":17,"adName":"抖阴漫画","fileType":"up","imageUrl":"/Init/Zone/3/4_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4},{"adId":18,"adName":"免费AV动漫","fileType":"up","imageUrl":"/Init/Zone/3/4_7.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":4}]},{"adGroupId":5,"sort":30,"adGroupName":"sm-tab直播","adGroupAlias":"直播","adGroupKey":"tabLive","adList":[{"adId":19,"adName":"伊人直播","fileType":"up","imageUrl":"/Init/Zone/3/5_1.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":5},{"adId":20,"adName":"青草直播","fileType":"up","imageUrl":"/Init/Zone/3/5_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":5}]},{"adGroupId":6,"sort":40,"adGroupName":"sm-tab游戏","adGroupAlias":"游戏","adGroupKey":"tabGame","adList":[{"adId":21,"adName":"多米体育","fileType":"up","imageUrl":"/Init/Zone/3/6_1.png","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":22,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/3/6_2.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":23,"adName":"澳门新葡京","fileType":"up","imageUrl":"/Init/Zone/3/6_3.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":24,"adName":"官方威尼斯人","fileType":"up","imageUrl":"/Init/Zone/3/6_4.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":25,"adName":"太阳城集团","fileType":"up","imageUrl":"/Init/Zone/3/6_5.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6},{"adId":26,"adName":"永利娱乐城","fileType":"up","imageUrl":"/Init/Zone/3/6_6.gif","url":"https://www.bilibili.com","extension":{},"adGroupId":6}]}],"zoneName":"标签页","zoneKey":"tab"},{"zoneId":4,"adGroup":[{"adGroupId":7,"sort":0,"adGroupName":"sm-推荐","adGroupAlias":"下载推荐","adGroupKey":"recommend","adList":[{"adId":27,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"4257004"}},"adGroupId":7},{"adId":28,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/4/7_1.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"831204"}},"adGroupId":7},{"adId":29,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"热门","times":"2196780"}},"adGroupId":7},{"adId":30,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"1834574"}},"adGroupId":7},{"adId":31,"adName":"免费X站","fileType":"up","imageUrl":"/Init/Zone/4/7_2.png","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"8751654"}},"adGroupId":7},{"adId":32,"adName":"91免费版","fileType":"up","imageUrl":"/Init/Zone/4/7_3.gif","url":"https://www.bilibili.com","extension":{"7":{"tag":"推荐","times":"722524"}},"adGroupId":7}]}],"zoneName":"推荐","zoneKey":"recommend"},{"zoneId":5,"adGroup":[{"adGroupId":8,"sort":0,"adGroupName":"sm-约会","adGroupAlias":"","adGroupKey":"date","adList":[{"adId":33,"adName":"约会1","fileType":"up","imageUrl":"/Init/Zone/5/1.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"在校学生","tag2":"寻求刺激","tag3":"潮吹喷水","age":"19","height":"163cm","cup":"B杯罩","district":"上海"}},"adGroupId":8},{"adId":34,"adName":"约会2","fileType":"up","imageUrl":"/Init/Zone/5/2.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"清纯妹妹","tag2":"可盐可甜","tag3":"乖巧可爱","age":"24","height":"168cm","cup":"C杯罩","district":"成都"}},"adGroupId":8},{"adId":35,"adName":"约会3","fileType":"up","imageUrl":"/Init/Zone/5/3.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"公司秘书","tag2":"野性放荡","tag3":"反差美女","age":"24","height":"172cm","cup":"D杯罩","district":"北京"}},"adGroupId":8},{"adId":36,"adName":"约会4","fileType":"up","imageUrl":"/Init/Zone/5/4.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"超级辣妈","tag2":"老公不在","tag3":"家里可约","age":"25","height":"163cm","cup":"C杯罩","district":"深圳"}},"adGroupId":8},{"adId":37,"adName":"约会5","fileType":"up","imageUrl":"/Init/Zone/5/5.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"极品御姐","tag2":"粉嫩无毛","tag3":"酒店可约","age":"27","height":"174cm","cup":"F杯罩","district":"全国"}},"adGroupId":8},{"adId":38,"adName":"约会6","fileType":"up","imageUrl":"/Init/Zone/5/6.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"兼职约啪","tag2":"在校学生","tag3":"交友线下","age":"19","height":"167cm","cup":"B杯罩","district":"成都"}},"adGroupId":8},{"adId":39,"adName":"约会7","fileType":"up","imageUrl":"/Init/Zone/5/7.png","url":"https://www.bilibili.com","extension":{"8":{"tag1":"姐妹花❀","tag2":"性感双飞","tag3":"随时可约","age":"23","height":"170cm","cup":"C杯罩","district":"全国"}},"adGroupId":8}]}],"zoneName":"约会","zoneKey":"date"},{"zoneId":6,"adGroup":[{"adGroupId":9,"sort":0,"adGroupName":"sm-底部浮动","adGroupAlias":"","adGroupKey":"bottomFloat","adList":[{"adId":40,"adName":"底部浮漂","fileType":"up","imageUrl":"/Init/Zone/6/1.gif","url":"https://www.bilibili.com","extension":{"9":{"title":"上门做爱","description":"打造最高端的情色盛宴"},"36":{"title":"上门做爱","description":"打造最高端的情色盛宴"},"37":{"title":"上门做爱","description":"打造最高端的情色盛宴"}},"adGroupId":9}]}],"zoneName":"底部浮动","zoneKey":"bottomFloat"}]},"systemTimestamp":1698751286,"systemDateTime":"2023-10-31 19:21:26","msg":"success"})
     */
    public function statistics()
    {
        $param = $this->request()->getRequestParam();
        try {
            $page = null;

            if (isset($param['pageName']) && $param['pageName']) {
                $page = PageModel::create()->getByCache($param['pageName']);
            }


            if (!$page) {
                $page = PageModel::create()->getByCache('index.html');
            }

            if (!$page) {
                throw new Exception('无效的页面参数', Status::CODE_BAD_REQUEST);
            }
            $config = ConfigModel::create()->getConfigValueList(array_merge(
                AppConfigKey::ALL_KEY,
            ));
            $h5PageUrl = $config['H5PageUrl'];
            $navCode = $page->visible(['navCode']);
            $result['navCode'] = $navCode['navCode'];
            $result['h5PageUrl'] = $h5PageUrl;
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 记录页面访问
     * @Api(name="记录页面访问",path="/Api/Home/Index/recordView")
     * @ApiDescription("记录页面访问，因为很多原因需要单独调用。1.导航只允许使用纯静态html 2.因是纯静态html，是否ip扣量要在前端判断")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字")
     * @Param(name="state", alias="ip状态", type="int", required="", inArray=[1, 0], description="ip统计状态，为了不被看名字看出来，1.有效 0.扣量")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function recordView()
    {
        $param = $this->request()->getRequestParam();

        try {
            $page = null;
            $param['state'] = intval($param['state']);

            if (isset($param['pageName']) && $param['pageName']) {
                $page = PageModel::create()->getByCache($param['pageName']);
            }

            if (!$page) {
                // 2023-12-19 改为自动创建渠道
                list($page,) = AutoChannelService::getInstance()->autoCreatePageAndChannel($param['pageName']);

                // $page = PageModel::create()->getByCache('index.html');
            }

            // 2023-11-13 新增页面状态判断，非启用状态的页面不统计。
            if (!$page || $page['status'] != PageModel::STATE_NORMAL) {
                throw new Exception('无效的页面参数', Status::CODE_BAD_REQUEST);
            }

           PageViewService::getInstance()->recordView([
                'page' => $page,
                'ip' => $this->clientRealIP(),
                'date' => date('Y-m-d'),
                'ipState' => $param['state'],
            ]);
            $result = true;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $this->clientRealIP(), Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 记录落地页访问
     * @Api(name="记录落地页访问",path="/Api/Home/Index/landPageView")
     * @ApiDescription("记录落地页访问，因为很多原因需要单独调用。1.导航只允许使用纯静态html 2.因是纯静态html，是否ip扣量要在前端判断")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function landPageView()
    {
        $param = $this->request()->getRequestParam();

        try {
            PageViewService::getInstance()->landPageView([
                'ip' => $this->clientRealIP(),
                'date' => date('Y-m-d'),
                'channelKey' => $param['channelKey'],
            ]);

            $result = true;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 记录落地页点击跳转
     * @Api(name="记录落地页点击跳转",path="/Api/Home/Index/landPageClick")
     * @ApiDescription("记录落地页点击跳转，因为很多原因需要单独调用。1.导航只允许使用纯静态html 2.因是纯静态html，是否ip扣量要在前端判断")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function landPageClick()
    {
        $param = $this->request()->getRequestParam();

        try {
            PageViewService::getInstance()->landPageClick([
                'ip' => $this->clientRealIP(),
                'date' => date('Y-m-d'),
                'channelKey' => $param['channelKey'],
            ]);

            $result = true;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 记录h5导航页面访问
     * @Api(name="记录h5导航页面访问",path="/Api/Home/Index/dhView")
     * @ApiDescription("记录h5导航页面访问")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", optional="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function dhView()
    {
//        $param = $this->request()->getRequestParam();
//
//        try {
//            PageViewService::getInstance()->h5PageView([
//                'ip' => $this->clientRealIP(),
//                'date' => date('Y-m-d'),
//                'channelKey' => $param['channelKey'],
//                'name' => 'dh',
//            ]);
//
//            $result = true;
//
//        } catch (Throwable $e) {
//            return $this->writeJson($e->getCode(), [], $e->getMessage());
//        }
//
        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * 记录h5播放器页面访问
     * @Api(name="记录h5播放器页面访问",path="/Api/Home/Index/welfareView")
     * @ApiDescription("记录h5播放器页面访问")
     * @Method(allow=["GET", "POST"])
     * @Param(name="channelKey", alias="渠道key", type="string", required="", mbLengthMin="1", description="渠道key")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1698657921,"systemDateTime":"2023-10-30 17:25:21","msg":"success"})
     */
    public function welfareView()
    {
//        $param = $this->request()->getRequestParam();
//
//        try {
//            PageViewService::getInstance()->h5PageView([
//                'ip' => $this->clientRealIP(),
//                'date' => date('Y-m-d'),
//                'channelKey' => $param['channelKey'],
//                'name' => 'welfare',
//            ]);
//
//            $result = true;
//
//        } catch (Throwable $e) {
//            return $this->writeJson($e->getCode(), [], $e->getMessage());
//        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 首页预览数据
     * @Api(name="首页预览数据",path="/Api/Home/Index/preview")
     * @ApiDescription("首页预览数据")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageTemplateId", alias="模板id", type="int", required="", min="1", description="模板id")
     * @ApiSuccess({"code":200,"result":{"page":{"pageId":0,"pageName":"preview","pageTemplateId":"1","code":""},"template":{"pageTemplateId":1,"pageTemplateName":"默认","pageTemplateKey":"default"},"config":{"WebsiteTitle":"ES导航","WebsiteKeywords":"ES导航关键字","WebsiteDescription":"ES导航描述","WebsiteContact":"广告联系TG:XX","CDN":""},"templateData":[{"zoneId":1,"adGroup":[{"adGroupId":1,"sort":0,"adGroupName":"顶部浮动","adGroupAlias":"","adGroupKey":"topFloat","adList":[{"adId":1,"adName":"顶部浮漂","fileType":"up","imageUrl":"/Init/Zone/1/1.gif","extension":[],"adGroupId":1}]}],"zoneName":"顶部浮动","zoneKey":"topFloat"},{"zoneId":2,"adGroup":[{"adGroupId":2,"sort":0,"adGroupName":"横幅","adGroupAlias":"","adGroupKey":"banner","adList":[{"adId":2,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/2/2.gif","extension":[],"adGroupId":2},{"adId":3,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/2/3.gif","extension":[],"adGroupId":2}]}],"zoneName":"横幅","zoneKey":"banner"},{"zoneId":3,"adGroup":[{"adGroupId":3,"sort":10,"adGroupName":"tab热门","adGroupAlias":"热门","adGroupKey":"tabHot","adList":[{"adId":4,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","extension":[],"adGroupId":3},{"adId":5,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/3/3_2.gif","extension":[],"adGroupId":3},{"adId":6,"adName":"珊瑚直播","fileType":"up","imageUrl":"/Init/Zone/3/3_3.gif","extension":[],"adGroupId":3},{"adId":7,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","extension":[],"adGroupId":3},{"adId":8,"adName":"情欲直播","fileType":"up","imageUrl":"/Init/Zone/3/3_5.gif","extension":[],"adGroupId":3},{"adId":9,"adName":"成人抖音","fileType":"up","imageUrl":"/Init/Zone/3/3_6.gif","extension":[],"adGroupId":3},{"adId":10,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","extension":[],"adGroupId":3},{"adId":11,"adName":"妹团直播","fileType":"up","imageUrl":"/Init/Zone/3/3_8.gif","extension":[],"adGroupId":3}]},{"adGroupId":4,"sort":20,"adGroupName":"tab视频","adGroupAlias":"视频","adGroupKey":"tabVideo","adList":[{"adId":12,"adName":"西门视频","fileType":"up","imageUrl":"/Init/Zone/3/4_1.gif","extension":[],"adGroupId":4},{"adId":13,"adName":"快色视频","fileType":"up","imageUrl":"/Init/Zone/3/4_2.png","extension":[],"adGroupId":4},{"adId":14,"adName":"樱桃视频","fileType":"up","imageUrl":"/Init/Zone/3/4_3.gif","extension":[],"adGroupId":4},{"adId":15,"adName":"pilipili","fileType":"up","imageUrl":"/Init/Zone/3/4_4.gif","extension":[],"adGroupId":4},{"adId":16,"adName":"AV破解版","fileType":"up","imageUrl":"/Init/Zone/3/4_5.gif","extension":[],"adGroupId":4},{"adId":17,"adName":"抖阴漫画","fileType":"up","imageUrl":"/Init/Zone/3/4_6.gif","extension":[],"adGroupId":4},{"adId":18,"adName":"免费AV动漫","fileType":"up","imageUrl":"/Init/Zone/3/4_7.gif","extension":[],"adGroupId":4}]},{"adGroupId":5,"sort":30,"adGroupName":"tab直播","adGroupAlias":"直播","adGroupKey":"tabLive","adList":[{"adId":19,"adName":"伊人直播","fileType":"up","imageUrl":"/Init/Zone/3/5_1.gif","extension":[],"adGroupId":5},{"adId":20,"adName":"青草直播","fileType":"up","imageUrl":"/Init/Zone/3/5_2.gif","extension":[],"adGroupId":5}]},{"adGroupId":6,"sort":40,"adGroupName":"tab游戏","adGroupAlias":"游戏","adGroupKey":"tabGame","adList":[{"adId":21,"adName":"多米体育","fileType":"up","imageUrl":"/Init/Zone/3/6_1.png","extension":[],"adGroupId":6},{"adId":22,"adName":"开元棋牌","fileType":"up","imageUrl":"/Init/Zone/3/6_2.gif","extension":[],"adGroupId":6},{"adId":23,"adName":"澳门新葡京","fileType":"up","imageUrl":"/Init/Zone/3/6_3.gif","extension":[],"adGroupId":6},{"adId":24,"adName":"官方威尼斯人","fileType":"up","imageUrl":"/Init/Zone/3/6_4.gif","extension":[],"adGroupId":6},{"adId":25,"adName":"太阳城集团","fileType":"up","imageUrl":"/Init/Zone/3/6_5.gif","extension":[],"adGroupId":6},{"adId":26,"adName":"永利娱乐城","fileType":"up","imageUrl":"/Init/Zone/3/6_6.gif","extension":[],"adGroupId":6}]}],"zoneName":"标签页","zoneKey":"tab"},{"zoneId":4,"adGroup":[{"adGroupId":7,"sort":0,"adGroupName":"推荐","adGroupAlias":"下载推荐","adGroupKey":"recommend","adList":[{"adId":27,"adName":"免费约炮","fileType":"up","imageUrl":"/Init/Zone/3/3_1.gif","extension":{"7":{"tag":"热门","times":"4257004"}},"adGroupId":7},{"adId":28,"adName":"上门做爱","fileType":"up","imageUrl":"/Init/Zone/4/7_1.gif","extension":{"7":{"tag":"热门","times":"831204"}},"adGroupId":7},{"adId":29,"adName":"蜜恋直播","fileType":"up","imageUrl":"/Init/Zone/3/3_4.gif","extension":{"7":{"tag":"热门","times":"2196780"}},"adGroupId":7},{"adId":30,"adName":"免费P站","fileType":"up","imageUrl":"/Init/Zone/3/3_7.gif","extension":{"7":{"tag":"推荐","times":"1834574"}},"adGroupId":7},{"adId":31,"adName":"免费X站","fileType":"up","imageUrl":"/Init/Zone/4/7_2.png","extension":{"7":{"tag":"推荐","times":"8751654"}},"adGroupId":7},{"adId":32,"adName":"91免费版","fileType":"up","imageUrl":"/Init/Zone/4/7_3.gif","extension":{"7":{"tag":"推荐","times":"722524"}},"adGroupId":7}]}],"zoneName":"推荐","zoneKey":"recommend"},{"zoneId":5,"adGroup":[{"adGroupId":8,"sort":0,"adGroupName":"约会","adGroupAlias":"","adGroupKey":"date","adList":[{"adId":33,"adName":"约会1","fileType":"up","imageUrl":"/Init/Zone/5/1.png","extension":{"8":{"tag1":"在校学生","tag2":"寻求刺激","tag3":"潮吹喷水","age":"19","height":"163cm","cup":"B杯罩","district":"上海"}},"adGroupId":8},{"adId":34,"adName":"约会2","fileType":"up","imageUrl":"/Init/Zone/5/2.png","extension":{"8":{"tag1":"清纯妹妹","tag2":"可盐可甜","tag3":"乖巧可爱","age":"24","height":"168cm","cup":"C杯罩","district":"成都"}},"adGroupId":8},{"adId":35,"adName":"约会3","fileType":"up","imageUrl":"/Init/Zone/5/3.png","extension":{"8":{"tag1":"公司秘书","tag2":"野性放荡","tag3":"反差美女","age":"24","height":"172cm","cup":"D杯罩","district":"北京"}},"adGroupId":8},{"adId":36,"adName":"约会4","fileType":"up","imageUrl":"/Init/Zone/5/4.png","extension":{"8":{"tag1":"超级辣妈","tag2":"老公不在","tag3":"家里可约","age":"25","height":"163cm","cup":"C杯罩","district":"深圳"}},"adGroupId":8},{"adId":37,"adName":"约会5","fileType":"up","imageUrl":"/Init/Zone/5/5.png","extension":{"8":{"tag1":"极品御姐","tag2":"粉嫩无毛","tag3":"酒店可约","age":"27","height":"174cm","cup":"F杯罩","district":"全国"}},"adGroupId":8},{"adId":38,"adName":"约会6","fileType":"up","imageUrl":"/Init/Zone/5/6.png","extension":{"8":{"tag1":"兼职约啪","tag2":"在校学生","tag3":"交友线下","age":"19","height":"167cm","cup":"B杯罩","district":"成都"}},"adGroupId":8},{"adId":39,"adName":"约会7","fileType":"up","imageUrl":"/Init/Zone/5/7.png","extension":{"8":{"tag1":"姐妹花❀","tag2":"性感双飞","tag3":"随时可约","age":"23","height":"170cm","cup":"C杯罩","district":"全国"}},"adGroupId":8}]}],"zoneName":"约会","zoneKey":"date"},{"zoneId":6,"adGroup":[{"adGroupId":9,"sort":0,"adGroupName":"底部浮动","adGroupAlias":"","adGroupKey":"bottomFloat","adList":[{"adId":40,"adName":"底部浮漂","fileType":"up","imageUrl":"/Init/Zone/6/1.gif","extension":{"9":{"title":"上门做爱","description":"打造最高端的情色盛宴"}},"adGroupId":9}]}],"zoneName":"底部浮动","zoneKey":"bottomFloat"}]},"systemTimestamp":1687522596,"systemDateTime":"2023-06-23 20:16:36","msg":"success"})
     */
    public function preview()
    {
        $param = $this->request()->getRequestParam();

        try {
            $template = PageTemplateModel::create()->get($param['pageTemplateId']);

            # TODO: 虽然这里还没改，但是因为已经弃用这个预览功能了，所以也就没更新
            // 如果要重新做的话注意一下这里的数据要和index接口保持一致。
            $templateData = PageService::getInstance()->getTemplateData($param['pageTemplateId']);
            $config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_WEBSITE);

            $result = [
                'page' => [
                    'pageId' => 0,
                    'pageName' => 'preview',
                    'pageTemplateId' => $param['pageTemplateId'],
                    'code' => '',
                ],
                'template' => $template->visible(['pageTemplateId', 'pageTemplateKey']),
                'config' => $config,
                'templateData' => $templateData,
            ];

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 点击
     * @Api(name="点击",path="/Api/Home/Index/click")
     * @ApiDescription("点击")
     * @Method(allow=["GET", "POST"])
     * @Param(name="adId", alias="广告id", type="int", required="", min="1", description="广告id")
     * @Param(name="pageName", alias="页面名字", type="string", required="", mbLengthMin="1", description="页面名字")
     * @Param(name="deviceId", alias="设备id", type="string", required="", mbLengthMin="1", description="设备id，原生用真实的，h5用https://github.com/fingerprintjs/fingerprintjs")
     * @Param(name="screen", alias="屏幕宽高", type="string", required="", mbLengthMin="1", description="格式举例：390x844，中间是英文字母x")
     * @ApiSuccess({"code":200,"result":{"url":"/"},"systemTimestamp":1686741972,"systemDateTime":"2023-06-14 19:26:12","msg":"success"})
     */
    public function echo($str){
        return $this->writeJson(Status::CODE_OK, $str, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function click()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();

            // 这个是测试用的
            if (Core::getInstance()->runMode() == 'dev' && isset($param['ip'])) {
                $ip = $param['ip'];
            }

            $page = PageModel::create()->getByCache($param['pageName']);

            if (!$page) {
                // 2023-12-19 改为自动创建渠道
                list($page,) = AutoChannelService::getInstance()->autoCreatePageAndChannel($param['pageName']);

                // $page = PageModel::create()->getByCache('index.html');
            }
            
            // 2023-11-13 新增页面状态判断，非启用状态的页面不统计。
            if (!$page || $page['status'] != PageModel::STATE_NORMAL) {
                throw new Exception('无效的页面参数', Status::CODE_BAD_REQUEST);
            }

            // 虽然最终存储任然是字符串，这里确保参数格式没有问题。
            $screen = explode('x', $param['screen']);
            if (count($screen) != 2) {
                throw new Exception('无效的屏幕参数', Status::CODE_BAD_REQUEST);
            }

            //点击日志
            $logData = $param;
            $logData['ip'] = $ip;
            LogHandler::getInstance()->logCustomFile(json_encode($logData, JSON_UNESCAPED_UNICODE), 'Click/' . '/click-v2');

            AdService::getInstance()->recordClickV2([
                'adId' => $param['adId'],
                'page' => $page,
                'screen' => ['width' => $screen[0], 'height' => $screen[1]],
                'ip' => $ip,
                'deviceId' => $param['deviceId'],
            ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $param['adId'], Status::getReasonPhrase(Status::CODE_OK));
    }


    /**
     * iptest
     * @Api(name="iptest",path="/Api/Home/Index/iptest")
     * @ApiDescription("iptest")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":{"url":"/"},"systemTimestamp":1686741972,"systemDateTime":"2023-06-14 19:26:12","msg":"success"})
     */
    public function iptest()
    {
        try {
            $ip = $this->clientRealIP();
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $ip, Status::getReasonPhrase(Status::CODE_OK));
    }
       //上传图片/视频
       public function upload()
       {
           try {
               $result = AwsOssService::getInstance()->uploadImage($this->request(), "");
           } catch (\Throwable $e) {
               return $this->writeJson($e->getCode(), [], $e->getMessage());
           }
           return $this->writeJson(Status::CODE_OK, $result, '上传成功');
       }
    public function getDownUrl(){
        $param = $this->request()->getRequestParam();
        try {
            $downUrl="";
            $channelData=ChannelNewModel::create()->where("channelKey",[$param["key"],"index.html"],"IN")->getAll();

            if(count($channelData["list"])>1){
                foreach($channelData["list"] as $k=>$v){
                    if($v->channelKey==$param["key"]){
                        if($param["mobile_type"]=="1"){
                            $downUrl=$v->androidDownUrl;
                        }else{
                            $downUrl=$v->iosDownUrl;
                        }
                    }
                } 
            }else{
                foreach($channelData["list"] as $k1=>$v1){
                    if($v1->channelKey=="index.html"){
                            if($param["mobile_type"]=="1"){
                                $downUrl=$v1->androidDownUrl;
                            }else{
                                $downUrl=$v1->iosDownUrl;
                            }
                    }
                }
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, ["downUrl"=>$downUrl], Status::getReasonPhrase(Status::CODE_OK));
    }
}