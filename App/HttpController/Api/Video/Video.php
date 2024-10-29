<?php

namespace App\HttpController\Api\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\RedisDb;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserVideoRecordModel;
use App\Model\Video\TypeModel;
use App\Model\Video\VideoModel;
use App\Model\Video\VideoNewModel;
use App\RedisKey\Video\VideoKey;
use App\Service\Video\TypeService;
use App\Service\Video\VideoService;
use EasySwoole\Http\Message\Status;
use EasySwoole\HttpAnnotation\AnnotationTag\Api;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiFail;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroup;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupAuth;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiGroupDescription;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiRequestExample;
use EasySwoole\HttpAnnotation\AnnotationTag\ApiSuccess;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;
use EasySwoole\ORM\DbManager;
use Aws\S3\S3Client;
use App\Model\Common\ConfigModel;
use App\Enum\ConfigKey\OssConfigKey;
use Exception;
use Throwable;

/**
 * Class Video
 * @package App\HttpController\Api\Video
 * @ApiGroup(groupName="影视区 Video/Video")
 * @ApiGroupDescription("影视区相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Video extends UserBase
{
    public $s3Client = null;
    public $s3Config = [];
    /**
     * 影视首页
     * @Api(name="影视首页",path="/Api/Video/Video/index")
     * @ApiDescription("影视首页，首页只有第一页的数据，所以没有分页，但是根据不同的首页需要的条数不一样，所以只传条数即可。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"typeList":[{"typeId":3,"typeName":"综艺","subType":[{"typeId":38,"typeName":"综艺"},{"typeId":39,"typeName":"真人秀"},{"typeId":40,"typeName":"脱口秀"},{"typeId":41,"typeName":"音乐"},{"typeId":42,"typeName":"选秀"},{"typeId":43,"typeName":"其他"}],"subArea":["大陆","香港","台湾","美国","法国","英国","日本","韩国","德国","泰国","印度","意大利","西班牙","加拿大","其他"],"subLang":["国语","英语","粤语","闽南语","韩语","日语","法语","德语","其他"],"subYear":["2023","2022","2021","2020","2019","2018","2017","2016","2015","2014","2013","2012","2011","2010"]},{"typeId":2,"typeName":"连续剧","subType":[{"typeId":16,"typeName":"国产剧"},{"typeId":17,"typeName":"港澳剧"},{"typeId":18,"typeName":"日剧"},{"typeId":19,"typeName":"欧美剧"},{"typeId":20,"typeName":"台湾剧"},{"typeId":21,"typeName":"泰剧"},{"typeId":22,"typeName":"韩剧"}],"subArea":["大陆","韩国","香港","台湾","日本","美国","泰国","英国","新加坡","其他"],"subLang":["国语","英语","粤语","闽南语","韩语","日语","其他"],"subYear":["2023","2022","2021","2020","2019","2018","2017","2016","2015","2014","2013","2012","2011","2010"]},{"typeId":1,"typeName":"电影","subType":[{"typeId":6,"typeName":"动作片"},{"typeId":7,"typeName":"喜剧片"},{"typeId":8,"typeName":"爱情片"},{"typeId":9,"typeName":"科幻片"},{"typeId":10,"typeName":"恐怖片"},{"typeId":11,"typeName":"剧情片"},{"typeId":12,"typeName":"战争片"},{"typeId":13,"typeName":"纪录片"},{"typeId":14,"typeName":"伦理片"},{"typeId":15,"typeName":"动漫电影"}],"subArea":["大陆","香港","台湾","美国","法国","英国","日本","韩国","德国","泰国","印度","意大利","西班牙","加拿大","其他"],"subLang":["国语","英语","粤语","闽南语","韩语","日语","法语","德语","其他"],"subYear":["2023","2022","2021","2020","2019","2018","2017","2016","2015","2014","2013","2012","2011","2010"]},{"typeId":4,"typeName":"动漫","subType":[{"typeId":36,"typeName":"国产动漫"},{"typeId":37,"typeName":"日本动漫"}],"subArea":["大陆","香港","台湾","美国","法国","英国","日本","韩国","德国","泰国","印度","意大利","西班牙","加拿大","其他"],"subLang":["国语","英语","粤语","闽南语","韩语","日语","法语","德语","其他"],"subYear":["2023","2022","2021","2020","2019","2018","2017","2016","2015","2014","2013","2012","2011","2010"]},{"typeId":5,"typeName":"体育赛事","subType":[],"subArea":[],"subLang":[],"subYear":[]}],"videoList":{"bannerList":[{"vodId":1282,"vodName":"惊天侠盗团","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/4bd2ca766c563faa665ac69521cec477.jpg","vodRemarks":"HD"},{"vodId":189,"vodName":"巨齿鲨2：深渊","vodPic":"https://image.maimn.com/cover/1e1662a930daa0e9a8d51798bc991885.jpg","vodRemarks":"高清版"}],"topList":[{"vodId":1090,"vodName":"72小时-黄金行动","vodPic":"https://image.smxjysm.com/cover/f320924d8b6d15e190ed513cca7e715e.jpg","vodRemarks":"正片"},{"vodId":1081,"vodName":"奇袭400高地","vodPic":"https://image.smxjysm.com/cover/af52734be1eb8bf0a7f6f8a100da2b19.jpg","vodRemarks":"正片"},{"vodId":1071,"vodName":"来日方长","vodPic":"https://image.smxjysm.com/cover/54c295687bc2a7b6d2e9d61e0e5d6f66.jpg","vodRemarks":"正片"},{"vodId":378,"vodName":"吸血鬼生活 第五季","vodPic":"https://image.maimn.com/cover/896f3c065d05cefcf3fe2852a7875109.jpg","vodRemarks":"第5集"},{"vodId":191,"vodName":"古战场传奇 第四季","vodPic":"https://image.maimn.com/cover/07271cced87b66be1b7d5c353563bd09.jpg","vodRemarks":"第13集完结"}],"latestList":[{"vodId":1282,"vodName":"惊天侠盗团","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/4bd2ca766c563faa665ac69521cec477.jpg","vodRemarks":"HD"},{"vodId":1281,"vodName":"民宿的秘密佐料","vodPic":"https://pic.wujinpp.com/upload/vod/20230809-1/ac74da644da9ec000698cacf3c7949be.jpg","vodRemarks":"更新至03集"},{"vodId":1278,"vodName":"反派第二季","vodPic":"https://pic.wujinpp.com/upload/vod/20230731-1/8e122c8f899a26e9e54423dde958e6c1.jpg","vodRemarks":"更新至03集"},{"vodId":1277,"vodName":"活着2023","vodPic":"https://pic.wujinpp.com/upload/vod/20230717-1/aeb383b754bb424abcb3d9b9dde6db70.jpg","vodRemarks":"更新至05集"},{"vodId":1276,"vodName":"陌生人2023","vodPic":"https://pic.wujinpp.com/upload/vod/20230717-1/33e36f9054430c31e93eea4e03873cf2.jpg","vodRemarks":"更新至09集"},{"vodId":1275,"vodName":"心跳韩版","vodPic":"https://pic.wujinpp.com/upload/vod/20230626-1/a8f77a67cf612752bed02fd7bd591987.jpg","vodRemarks":"更新至15集"}],"typeList":[{"typeName":"综艺","videoList":[{"vodId":37,"vodName":"朋友请吃饭","vodPic":"https://image.maimn.com/cover/24d510a6f87403bf5b83f64726c3fd90.jpg","vodRemarks":"第9期"},{"vodId":36,"vodName":"闪亮的日子第四季","vodPic":"https://image.maimn.com/cover/6af924ebe7eec2218f8d11d3dd582d04.jpg","vodRemarks":"第14期"},{"vodId":35,"vodName":"森林进化论","vodPic":"https://image.maimn.com/cover/5cc934df7430c81c8681c8f13d85ac85.jpg","vodRemarks":"第4期"},{"vodId":25,"vodName":"五十公里桃花坞3","vodPic":"https://image.maimn.com/cover/abb2e494d8466016a029303922ed70b8.jpg","vodRemarks":"陪你去看桃花坞"},{"vodId":18,"vodName":"你好，星期六 2023","vodPic":"https://image.maimn.com/cover/f9cb3124b5a22b133f21273ef2cb0695.jpg","vodRemarks":"第20230808期"},{"vodId":15,"vodName":"经典传奇","vodPic":"https://image.maimn.com/cover/705c03a1245566a3edb2d1c3ddcbb6ff.jpg","vodRemarks":"第20230808期"}]},{"typeName":"连续剧","videoList":[{"vodId":194,"vodName":"神探柯晨","vodPic":"https://image.maimn.com/cover/2c75db7849c08cf7e84dd2538c4e2f85.jpg","vodRemarks":"第44集完结"},{"vodId":113,"vodName":"郎君不如意","vodPic":"https://image.maimn.com/cover/4a249ffbfe961148eb31066bad622331.jpg","vodRemarks":"第27集"},{"vodId":110,"vodName":"小楼又东风","vodPic":"https://image.maimn.com/cover/68bf916468ff6f89dc59f62136059249.jpg","vodRemarks":"第46集完结"},{"vodId":104,"vodName":"莲花楼","vodPic":"https://image.maimn.com/cover/d23f1d1cd4efe77dac5482f856a9d12d.jpg","vodRemarks":"第30集"},{"vodId":103,"vodName":"紧急公关","vodPic":"https://image.maimn.com/cover/2d01216e288ff2a1a0fd90c4a4b6bd0c.jpg","vodRemarks":"第38集完结"},{"vodId":101,"vodName":"大宋少年志2","vodPic":"https://image.maimn.com/cover/e990939764541b9ff6da7657325ca346.jpg","vodRemarks":"第14集"}]},{"typeName":"电影","videoList":[{"vodId":1282,"vodName":"惊天侠盗团","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/4bd2ca766c563faa665ac69521cec477.jpg","vodRemarks":"HD"},{"vodId":189,"vodName":"巨齿鲨2：深渊","vodPic":"https://image.maimn.com/cover/1e1662a930daa0e9a8d51798bc991885.jpg","vodRemarks":"高清版"},{"vodId":1007,"vodName":"社畜向前冲","vodPic":"https://image.maimn.com/cover/05f49892786ce3bc342530b41d0b1c7d.jpg","vodRemarks":"正片"},{"vodId":410,"vodName":"捐躯","vodPic":"https://image.maimn.com/cover/88a6b952ba951c6f0692facc842d986e.jpg","vodRemarks":"正片"},{"vodId":1119,"vodName":"捐躯","vodPic":"https://image.smxjysm.com/cover/88a6b952ba951c6f0692facc842d986e.jpg","vodRemarks":"正片"},{"vodId":562,"vodName":"夺魂密令","vodPic":"https://image.maimn.com/cover/2a1be5f04845770c777cd2dcb8730c00.jpg","vodRemarks":"正片"}]},{"typeName":"动漫","videoList":[{"vodId":990,"vodName":"末世超级系统 第二季","vodPic":"https://image.maimn.com/cover/60e6ea8c63d0c5e6b8eecd289c4da78e.jpg","vodRemarks":"第3集"},{"vodId":63,"vodName":"灵剑尊","vodPic":"https://img.maimn.com/upload/vod/20220809-1/bbf0a22ceea450330c811734565ab1fe.jpg","vodRemarks":"第407集"},{"vodId":61,"vodName":"生而为猫","vodPic":"https://image.maimn.com/cover/e51c251189315a2b6b7eb9260a776241.jpg","vodRemarks":"第53集"},{"vodId":59,"vodName":"万界独尊","vodPic":"https://img.maimn.com/upload/vod/2021-06-30/16250195730.jpg","vodRemarks":"第154集"},{"vodId":58,"vodName":"冰火魔厨","vodPic":"https://img.maimn.com/upload/vod/2021-12-11/202112111639193088.jpg","vodRemarks":"第99集"},{"vodId":56,"vodName":"我可以修改万物时间线","vodPic":"https://image.maimn.com/cover/6c2201e67e0fdd52f99c6f5332c73f24.jpg","vodRemarks":"第26集"}]},{"typeName":"体育赛事","videoList":[{"vodId":1283,"vodName":"8月14日 23-24赛季西甲第1轮 赫塔费VS巴塞罗那","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/eae620828aab9ee2e23686d81a37ec49.jpg","vodRemarks":"HD"},{"vodId":1258,"vodName":"8月15日 23-24赛季英超第1轮 曼联VS狼队","vodPic":"https://pic.wujinpp.com/upload/vod/20230815-1/8cf1c4d435569d06625cea2fb2de293d.jpg","vodRemarks":"HD"}]}]}},"systemTimestamp":1692190611,"systemDateTime":"2023-08-16 20:56:51","msg":"OK"})
     */
    public function index()
    {
        $param = $this->request()->getRequestParam();

        try {
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $data = VideoService::getInstance()->getIndexData($pageSize);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 影视列表（同搜索，同分类筛选）
     * @Api(name="影视列表（同搜索，同分类筛选）",path="/Api/Video/Video/videoList")
     * @ApiDescription("影视列表（同搜索，同分类筛选）。说明：传影片名就是搜索操作，分类筛选就按照正常的id来传递，如果是选择'全部'则不需要传递这个分类的key和value，比如2级分类选全部，则不需要传递typeId。注意每页大小根据每行个数来传递。比如一行3个，就传18/24/30这种比较合适。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="typeParentId", alias="所属1级分类", type="int", optional="", min="1", description="所属1级分类")
     * @Param(name="typeId", alias="所属2级分类", type="int", optional="", min="1", description="所属2级分类")
     * @Param(name="vodArea", alias="地区", type="string", optional="", mbLengthMin="1", description="地区")
     * @Param(name="vodLang", alias="语言", type="string", optional="", mbLengthMin="1", description="语言")
     * @Param(name="vodYear", alias="年份", type="string", optional="", mbLengthMin="1", description="年份")
     * @Param(name="vodName", alias="影片名", type="string", optional="", mbLengthMin="1", description="影片名")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"vodId":1010,"vodName":"这座岛","vodPic":"https://image.maimn.com/cover/e2e3fb19cb1f4edf8c601bab9adbad86.jpg","vodRemarks":"正片"},{"vodId":1099,"vodName":"逃离夺命岛","vodPic":"https://image.smxjysm.com/cover/4a66ba27e2e2a6b9420c1011579a5f24.jpg","vodRemarks":"抢先版"},{"vodId":299,"vodName":"冰岛之光","vodPic":"https://image.maimn.com/cover/b7607d9f8da38a6cbdd4e32c50cf7eef.jpg","vodRemarks":"第1集完结"}],"options":{"vodName":"岛","typeId1":[1,2,3,4,5]}},"systemTimestamp":1692177224,"systemDateTime":"2023-08-16 17:13:44","msg":"OK"})
     */
    public function videoList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            /* 需要添加上video分类的所有顶级id begin */
            // 注意，不要把采集数据绑定在顶级，如果有，请重新添加数据。如果实在不行，那就不用管这部分数据的搜索。
            $typeId1 = TypeService::getInstance()->getVideoPidList();
            $typeId1 && $keyword['typeId1'] = $typeId1;
            /* 需要添加上video分类的所有顶级id end */

            if (isset($param['vodName']) && $param['vodName']) {
                // 这里是搜索
                $keyword['vodName'] = $param['vodName'];

                VideoService::getInstance()->setHotWords($param['vodName']);
            } else {
                // 这里是分类筛选
                isset($param['typeParentId']) && $param['typeParentId'] > 0 && $keyword['typeId1'] = $param['typeParentId'];
                isset($param['typeId']) && $param['typeId'] > 0 && $keyword['typeId'] = $param['typeId'];
                isset($param['vodArea']) && $param['vodArea'] && $keyword['vodArea'] = $param['vodArea'];
                isset($param['vodLang']) && $param['vodLang'] && $keyword['vodLang'] = $param['vodLang'];
                isset($param['vodYear']) && $param['vodYear'] && $keyword['vodYear'] = $param['vodYear'];
            }

            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic AS vodPic',
                'vod_remarks AS vodRemarks',
            ];

            $data = VideoModel::create()
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 影片详情
     * @Api(name="影片详情",path="/Api/Video/Video/videoDetail")
     * @ApiDescription("影片详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="影片id", type="int", required="", min="1", description="影片id")
     * @ApiSuccess({"code":200,"result":{"vodId":1124,"vodName":"领袖水准","vodDoubanScore":"7.0","vodRemarks":"正片","vodArea":"美国","vodClass":"动作,科幻,惊悚,动作片","vodBlurb":"梅尔·吉布森、弗兰克·格里罗商谈加盟动作惊悚片《领袖水准》(Boss Level)，乔·卡纳汉(《人狼大战》《天龙特攻队》)执导并编写剧本。格里罗将饰演一名退役特种部队老兵，被困在永无止境的死亡循环之","vodPlayFrom":"hnm3u8$$$gsm3u8$$$hym3u8","vodPlayUrl":"正片$https://hnzy.bfvvs.com/play/DdwlDmre/index.m3u8$$$正片$https://v.gsuus.com/play/7axmEvlb/index.m3u8$$$正片$https://1080p.huyall.com/play/kazoJKqb/index.m3u8","vodDirector":"乔·卡纳汉","vodActor":"梅尔·吉布森,安娜贝拉·沃丽丝,杨紫琼,弗兰克·格里罗,娜奥米·沃茨,郑肯","typeId":6,"typeName":"动作片","vodYear":"2020","vodContent":"<p>梅尔·吉布森、弗兰克·格里罗商谈加盟动作惊悚片《领袖水准》(Boss Level)，乔·卡纳汉(《人狼大战》《天龙特攻队》)执导并编写剧本。格里罗将饰演一名退役特种部队老兵，被困在永无止境的死亡循环之...</p>"},"systemTimestamp":1692086703,"systemDateTime":"2023-08-15 16:05:03","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"vodId":1270,"vodName":"物物语","vodDoubanScore":"0.0","vodRemarks":"更新至19集","vodArea":"日本","vodClass":"动作,爱情,动画,奇幻,日韩动漫","vodBlurb":"少年×少女×付丧神。\r\n　　经过漫长岁月的器物，最终其有了“心”，即为付丧神的诞生。\r\n　　因付丧神将重要的人夺走，因而憎恨著期的青年·岐兵马。\r\n　　与六名付丧神同住，像“家人”般爱着他们的少女·长","vodPlayFrom":"wjm3u8","vodPlayUrl":"第01集$https://new.qoqkkhy.com/20230110/YEPubuzL/index.m3u8#第02集$https://new.qoqkkhy.com/20230117/wuxKWQEM/index.m3u8#第03集$https://hot.qoqkkhy.com/20230123/2oID3uiM/index.m3u8#第04集$https://hot.qoqkkhy.com/20230130/obVr00xX/index.m3u8#第05集$https://hot.qoqkkhy.com/20230206/hldX8ev8/index.m3u8#第06集$https://hot.qoqkkhy.com/20230213/SIljQFwD/index.m3u8#第07集$https://hot.qoqkkhy.com/20230220/gsni8QvQ/index.m3u8#第08集$https://hot.qoqkkhy.com/20230227/sAOnY9oe/index.m3u8#第09集$https://hot.qoqkkhy.com/20230306/d3AcQBLN/index.m3u8#第10集$https://hot.qoqkkhy.com/20230313/krc6dHkQ/index.m3u8#第11集$https://s1.zoubuting.com/20230320/idpPwBTM/index.m3u8#第12集$https://s1.zoubuting.com/20230327/zwJb1b8L/index.m3u8#第13集$https://top.qoqkkhy.com/202307/04/q0i3bWVU453/video/index.m3u8#第14集$https://top.qoqkkhy.com/202307/11/EUTEs6a5Gs3/video/index.m3u8#第15集$https://top.qoqkkhy.com/202307/18/1P7DTdTije3/video/index.m3u8#第16集$https://top.qoqkkhy.com/202307/25/Uy9vacv2Bb3/video/index.m3u8#第17集$https://top.qoqkkhy.com/202308/01/c2TRpxQXEu3/video/index.m3u8#第18集$https://top.qoqkkhy.com/202308/08/5rjieCL2jj3/video/index.m3u8#第19集$https://top.qoqkkhy.com/202308/15/eWdN4R5zyB3/video/index.m3u8","vodDirector":"木村隆一,大川贵大","vodActor":"大塚刚央,高田忧希,泽城美雪,小林亲弘,上田丽奈,中岛良树,田中爱美,大西沙织,金光宣明,田渕将平,高桥伸也","typeId":4,"typeName":"动漫","vodYear":"2022","vodContent":"<p>少年×少女×付丧神。\r\n　　经过漫长岁月的器物，最终其有了“心”，即为付丧神的诞生。\r\n　　因付丧神将重要的人夺走，因而憎恨著期的青年·岐兵马。\r\n　　与六名付丧神同住，像“家人”般爱着他们的少女·长月牡丹。\r\n　　于千年之都的京都，两人相遇了，并一起同住。\r\n　　在前途多难的屋檐下，三者展开了交错的共同生活。\r\n　　人与物。羁绊与恋情的付丧神物语。</p>"},"systemTimestamp":1692086799,"systemDateTime":"2023-08-15 16:06:39","msg":"OK"})
     */
    public function videoDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = VideoModel::create()
                ->alias('v')
                ->field([
                    // 主内容
                    'vod_id AS vodId', // id
                    'vod_name AS vodName', // 片名
                    'vod_douban_score AS vodDoubanScore', // 豆瓣评分
                    'vod_remarks AS vodRemarks', // 影片备注 （正片，TC）
                    'vod_area AS vodArea', // 地区
                    'vod_class AS vodClass', // 分类
                    'vod_blurb AS vodBlurb', // 简介
                    'vod_play_from AS vodPlayFrom', // 观看路线
                    'vod_play_url AS vodPlayUrl', // 观看选集
                    // 简介内容
                    'vod_director AS vodDirector', // 导演
                    'vod_actor AS vodActor', // 演员
                    'v.type_id AS typeId', // 分类id
                    'type_name AS typeName', // 分类名
                    'vod_year AS vodYear', // 年代
                    'vod_area AS vodArea', // 地区
                    'vod_content AS vodContent', // 简介
                ])
                ->join(TypeModel::create()->getTableName() . ' AS t', 'v.type_id = t.type_id')
                ->where(['vod_status' => VideoModel::STATE_NORMAL])
                ->get($param['vodId']);

            if (!$data) {
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }

            $data['vodPlayFrom'] = VideoService::getInstance()->convertSourceText($data['vodPlayFrom']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 影片推荐列表
     * @Api(name="影片推荐列表",path="/Api/Video/Video/getRecommendedList")
     * @ApiDescription("影片推荐列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":883,"list":[{"vodId":990,"vodName":"末世超级系统 第二季","vodPic":"https://image.maimn.com/cover/60e6ea8c63d0c5e6b8eecd289c4da78e.jpg"},{"vodId":992,"vodName":"炼体十万层：都市篇","vodPic":"https://img.maimn.com/upload/vod/2021-11-04/202111041635996691.jpg"},{"vodId":991,"vodName":"仙帝入侵 动态漫画","vodPic":"https://image.maimn.com/cover/c0e865c81cab6a3aa12ab4e49f74589f.jpg"},{"vodId":1283,"vodName":"8月14日 23-24赛季西甲第1轮 赫塔费VS巴塞罗那","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/eae620828aab9ee2e23686d81a37ec49.jpg"},{"vodId":1282,"vodName":"惊天侠盗团","vodPic":"https://pic.wujinpp.com/upload/vod/20230814-1/4bd2ca766c563faa665ac69521cec477.jpg"},{"vodId":1281,"vodName":"民宿的秘密佐料","vodPic":"https://pic.wujinpp.com/upload/vod/20230809-1/ac74da644da9ec000698cacf3c7949be.jpg"}],"options":{"typeId1":{"0":0,"5":1,"15":2,"22":4}}},"systemTimestamp":1692093730,"systemDateTime":"2023-08-15 18:02:10","msg":"OK"})
     */
    public function getRecommendedList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            // 2023-09-18 取消7天以内条件
            // 推荐数据默认为7天以内的
            // $keyword['vodTimeAdd'] = [time() - (7 * 86400), '>'];

            // 推荐列表直接设置顶级id为普通视频
            // 然后按照推荐级别排序
            $typeId1 = TypeService::getInstance()->getVideoPidList();
            $typeId1 && $keyword['typeId1'] = $typeId1;

            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic AS vodPic',
            ];

            $data = VideoModel::create()
                //->order('vod_level', 'DESC')
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 搜索热词列表
     * @Api(name="搜索热词列表",path="/Api/Video/Video/searchKeyWords")
     * @ApiDescription("搜索热词列表。")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":["dd","aaaa"],"systemTimestamp":1698069482,"systemDateTime":"2023-10-23 21:58:02","msg":"OK"})
     */
    public function searchKeyWords()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = VideoService::getInstance()->getHotWords();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 收藏影片
     * @Api(name="收藏影片",path="/Api/Video/Video/favoriteVideo")
     * @ApiDescription("收藏影片。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="影片id", type="int", required="", min="1", description="影片id")
     * @Param(name="type", alias="类型", type="int", description="类型")
     * @ApiSuccess()
     */
    public function favoriteVideo()
    {
        $param = $this->request()->getRequestParam();
        $videoId = trim($param['vodId']);
        $type = trim($param['type']);
        try {

            $userId = $this->who['userId'];
            if(empty($type)){
                $userVideoRecord = UserVideoRecordModel::create()
                    ->field(['userId', 'videoId', 'type'])
                    ->where(['userId' => $userId, 'videoId' => $videoId, 'type' => 1])
                    ->get();

                if (!$userVideoRecord) {
                    UserVideoRecordModel::create([
                        'userId' => $userId,
                        'videoId' => $videoId,
                        'type' => 1,
                        'createTime' => date('Y-m-d H:i:s'),
                        'updateTime' => date('Y-m-d H:i:s'),
                    ])->save();
                }
            }

            if($type){
                $userVideoRecord = UserVideoRecordModel::create()
                    ->field(['userId', 'videoId', 'type'])
                    ->where(['userId' => $userId, 'videoId' => $videoId, 'type' => $type])
                    ->get();
                if($userVideoRecord){
                     $res = UserVideoRecordModel::create()
                        ->where(['userId' => $userId, 'videoId' => $videoId, 'type' => $type])
                        ->destroy();
                }
            }


        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 收藏历史记录列表
     * @Api(name="收藏历史记录列表",path="/Api/Video/Video/videoRecordList")
     * @ApiDescription("收藏历史记录列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="type", alias="1收藏2历史", type="string",   description="1收藏2历史")
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess()
     */
    public function videoRecordList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $type = $param['type'];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['uv.type'] = $type;
            $keyword['uv.userId'] = $this->who['userId'];
            $data = UserVideoRecordModel::create()
                ->alias('uv')
                ->join(VideoModel::create()->getTableName() . ' AS v', 'v.vod_id = uv.videoId', 'LEFT')
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, ['vod_id as vodId', 'vod_name as vodName', 'vod_pic as vodPic','vod_score_num as vodScoreNum']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //获取免费视频  
    public function videoFree(){
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $videoModel=VideoNewModel::create()->alias('video');
            $videoType=TypeModel::create();
            $data = $videoModel
                ->join($videoType->getTableName() . ' AS type', 'type.type_id = video.type_id', 'LEFT')
                ->where(["type.is_free"=>1])
                ->where(["video.vod_status"=>1])
                ->order("video.vod_up","desc")
                ->getAll($page, $keyword, $pageSize, ['vod_id as vodId', 'video.type_id ', 'vod_name as vodName', 'vod_pic as vodPic','vod_score_num as vodScoreNum']);
               
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function videoChange(){
        $this->s3Config = ConfigModel::create()->getConfigValueByGroup(ConfigModel::GROUP_OSS);
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region' => $this->s3Config[OssConfigKey::AWS_S3_REGION],
            'endpoint' => $this->s3Config[OssConfigKey::AWS_S3_ENDPOINT],
            'credentials' => [
                'key' => $this->s3Config[OssConfigKey::AWS_S3_ACCESS_ID],
                'secret' => $this->s3Config[OssConfigKey::AWS_S3_ACCESS_KEY],
            ],
        ]);
       
        $videoModel=VideoNewModel::create();
        $data = $videoModel
            ->where("is_uppro",0)
            ->where("vod_status",1)
            ->order("vod_id","desc")
            ->limit(50)
            ->all([]);
              foreach($data as $k=>$v){
                $imgUrl=$v->vod_pic_thumb;
                $fileContent=file_get_contents($imgUrl);
                if($fileContent==""){
                 continue;
                }
                $fileName="upload/image/vodpic/".date("Y-m-d")."/".uniqid().".jpg";
                 $this->writeJson(Status::CODE_OK, $fileName, Status::getReasonPhrase(Status::CODE_OK));
                $res=$this->s3Client->putObject([
                    'Bucket' => $this->s3Config[OssConfigKey::AWS_S3_BUCKET],
                    'Key' => $fileName,
                    'Body' => base64_encode($fileContent), // 原生使用这个 fopen('/path/to/image.jpg', 'r'),
                    'ContentType' =>"image/jpeg", // 必须要加这个才能以图片返回。（否则是下载文件）
                ]);
                $fileName="/".$fileName;
                $videoModel->update(["vod_pic"=>$fileName,"vod_pic2"=>$fileName,"vod_pic_thumb"=>$fileName,"click"=>rand(1111,999999),"is_uppro"=>1],["vod_id"=>$v->vod_id]);
                $this->writeJson(Status::CODE_OK, $res, $v->vod_name."完成------------");
                }
            return $this->writeJson(Status::CODE_OK, [], Status::getReasonPhrase(Status::CODE_OK));
        
            // return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
        }
    
}