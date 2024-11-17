<?php

namespace App\HttpController\Api\Video;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\VideoConfigKey;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\Common\ConfigModel;
use App\Model\Common\ConfigNewModel;
use App\Model\User\UserVideoRecordModel;
use App\Model\Video\VideoModel;
use App\Model\Video\TypeModel;
use App\RedisKey\Navigation\TemplateKey;
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
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

/**
 * Class Adult
 * @package App\HttpController\Api\Video
 * @ApiGroup(groupName="成人影片 Video/Adult")
 * @ApiGroupDescription("成人影片相关的操作")
 */
class Adult extends UserBase
{
    /**
     * AV列表
     * @Api(name="AV列表",path="/Api/Video/Adult/adultList")
     * @ApiDescription("AV列表。av搜索也是这个接口，搜索传vodName。")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="vodName", alias="影片名", type="string", optional="", mbLengthMin="1", description="影片名")
     * @Param(name="typeId", alias="所属分组", type="int", optional="", min="1", description="所属分组")
     * @ApiSuccess({"code":200,"result":{"total":35,"list":[{"vodId":694,"vodName":"【少女破处】杰西卡·阿尔班卡少女在家呻吟玩弄小穴揉捏","vodPic":"https://ee.hghhh.com/videos/202307/26/64c0d0d1aaf5fd461efba2af/poster.jpg"},{"vodId":695,"vodName":"【少女破处】奥利维亚班德拉斯玩弄少女被破处搞得高潮","vodPic":"https://ee.hghhh.com/videos/202307/26/64c0cbdaaaf5fd461efab9d5/poster.jpg"}],"options":{"typeId":"30"}},"systemTimestamp":1691986274,"systemDateTime":"2023-08-14 12:11:14","msg":"OK"})
     */
    public function adultList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $orderType = '';
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            // 成人视频列表需要添加上av分类的顶级id
            $typeIdList = TypeService::getInstance()->getAdultIdList();
            $typeIdList && $keyword['typeId'] = $typeIdList;

            if (isset($param['vodName']) && $param['vodName']) {
                $keyword['vodName'] = $param['vodName'];

                VideoService::getInstance()->setAdultHotWords($param['vodName']);
            } else {
                if (isset($param['typeId']) && !in_array($param['typeId'], [47, 49])) { // 47 今日更新，49 热门，不用筛选分类
                    $keyword['typeId'] = $param['typeId'];
                }

                if (isset($param['typeId']) && $param['typeId'] == 49) {
                    $orderType = ['vod_id', 'DESC'];
                }
            }

            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic_thumb AS vodPic',
                'vod_pic2 AS vodPic2',
                'vod_play_url AS vodPlayUrl',
                'type_id as type_id'
            ];

            // $redis = RedisPool::defer();
            // $key = TemplateKey::adultPageCache($param['typeId'].'-'.$page);
            // $data = $redis->get($key);
            
            if (isset($param['vodName']) && $param['vodName']) {
                $data = VideoModel::create()
                    ->order('vod_id', 'DESC')
                    ->setOrderType($orderType)
                    ->getAll($page, $keyword, $pageSize, $field);
            }else{
                // if(!$data){
                    $data = VideoModel::create()
                        ->order('vod_id', 'DESC')
                        ->setOrderType($orderType)
                        ->getAll($page, $keyword, $pageSize, $field);
                //     $redis->set($key, $data, 7200);
                // }
            }



        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 成人影片详情
     * @Api(name="成人影片详情",path="/Api/Video/Adult/adultDetail")
     * @ApiDescription("成人影片详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="vodId", alias="影片id", type="int", required="", min="1", description="影片id")
     * @ApiSuccess({"code":200,"result":{"vodId":740,"vodName":"【無碼流出】我的妻子1849 No.1229 相川莫奈美 名人俱乐部麦妻子","vodPlayFrom":"dplayer","vodPlayUrl":"高清$https://ee.hghhh.com/videos/202308/01/64c7da3ed713b3460aa2aec5/c976b4/index.m3u8","vodApi":""},"systemTimestamp":1693376865,"systemDateTime":"2023-08-30 14:27:45","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"vodId":2678,"vodName":"【麻豆传媒】父亲花钱买下的女人","vodPlayFrom":"mdm3u8","vodPlayUrl":"fa803c115d7d50bfe239686ae9c1eb35","vodApi":"https://zbk.mochaav.com/?url=fa803c115d7d50bfe239686ae9c1eb35"},"systemTimestamp":1693376953,"systemDateTime":"2023-08-30 14:29:13","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"vodId":15033,"vodName":"【学生精选】杭州反差少女宋雯，3p虐操性爱私拍流出","vodPlayFrom":"mdm3u8","vodPlayUrl":"正片$e0621cc39fde4099aa167ec2c01d45b2","vodApi":"https://zbk.mochaav.com/?url=e0621cc39fde4099aa167ec2c01d45b2"},"systemTimestamp":1693391170,"systemDateTime":"2023-08-30 18:26:10","msg":"OK"})
     */
    public function adultDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = VideoModel::create()
                ->field([
                    'vod_id AS vodId',
                    'vod_name AS vodName',
                    'vod_play_from AS vodPlayFrom',
                    'vod_play_url AS vodPlayUrl',
                    'type_id AS typeId',
                    'click AS click',
                    'vod_time_add AS vod_time_add',
                    'is_aws AS is_aws',
                ])
                ->where(['vod_status' => VideoModel::STATE_NORMAL])
                ->get($param['vodId']);

            if (!$data) {
                throw new Exception('无效的vodId参数', Status::CODE_BAD_REQUEST);
            }

                
                    if($data["is_aws"]==1){
                       
                        $awsHost=ConfigNewModel::create()->where("cfgKey","AwsS3Host")->get();
                        $data["vodPlayUrl"]=$awsHost["cfgValue"].$data["vodPlayUrl"];
                    }

            $userVideoRecord = UserVideoRecordModel::create()
                ->field(['userId', 'videoId', 'type'])
                ->where(['userId' => $this->who['userId'], 'videoId' => $param['vodId'], 'type' => 2])
                ->get();

            $collectVideo = UserVideoRecordModel::create()
                ->field(['userId', 'videoId', 'type'])
                ->where(['userId' => $this->who['userId'], 'videoId' => $param['vodId'], 'type' => 1])
                ->get();
            if($collectVideo){
                $data['collect'] = 1;
            }else{
                $data['collect'] = 0;
            }

            if (!$userVideoRecord) {
                UserVideoRecordModel::create([
                    'userId' => $this->who['userId'],
                    'videoId' => $param['vodId'],
                    'type' => 2,
                    'createTime' => date('Y-m-d H:i:s'),
                    'updateTime' => date('Y-m-d H:i:s'),
                ])->save();
            }


            // 这里有个特殊的情况，因为增加了一个收费源，但是这个源没有视频地址，是md5的key,需要标记和返回接口。
            if ($data['vodPlayFrom'] == 'mdm3u8') {
                $vodApi = ConfigModel::create()->getConfigValue(VideoConfigKey::VideoApiZhenBuKa);
                // 接口数据格式和其他的都不一样，需要单独判断是哪一种。
                // 如果有$就分割，如果没有$就直接拼接。
                $pos = mb_strpos($data['vodPlayUrl'], '$');
                if ($pos !== false) {
                    $vodApi .= mb_substr($data['vodPlayUrl'], $pos + 1);
                } else {
                    $vodApi .= $data['vodPlayUrl'];
                }

                $data['vodApi'] = $vodApi;
            } else {
                $data['vodApi'] = '';
            }
            $data['isFree'] = 0;
            //查询type 是否是isfree
            $typeData=TypeModel::create()->where(["type_status"=>1,"is_free"=>1])->field("type_id")->all();
            $typeIdData=[];
            foreach($typeData as $typek=>$typev){
                $typeIdData[]=$typev->type_id;
            }
            if (in_array($data['typeId'], $typeIdData)) {
                $data['isFree'] = 1;
            }
            $data['vodPlayFrom'] = VideoService::getInstance()->convertSourceText($data['vodPlayFrom']);

            $data->where(['vod_id' => $data->vodId])->update(['vod_hits' => QueryBuilder::inc()]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * AV推荐列表
     * @Api(name="AV推荐列表",path="/Api/Video/Adult/getRecommendedList")
     * @ApiDescription("AV推荐列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="vodId", alias="影片id", type="int", optional="", min="1", description="影片id，成人影片推荐接口改为通过影片id，来推荐相同二级分类的列表。")
     * @ApiSuccess({"code":200,"result":{"total":342,"list":[{"vodId":746,"vodName":"【少女破处】丽莎图托哈刚成年被哥哥破了处好刺激","vodPic":"https://ee.hghhh.com/videos/202307/26/64c0d265d713b3460aead0b0/poster.jpg"},{"vodId":978,"vodName":"【中文字幕】ADN-480 我會安慰你的… 妃光莉","vodPic":"https://ee.hghhh.com/videos/202308/08/64d1bda8d713b3460a2e512d/poster.jpg"}],"options":{"typeId1":27}},"systemTimestamp":1691989624,"systemDateTime":"2023-08-14 13:07:04","msg":"OK"})
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

            // 2023-09-18 推荐列表改为观看影片的相同二级分类
            if (isset($param['vodId'])) {
                $typeId = VideoModel::create()->where(['vod_id' => $param['vodId']])->val('type_id');
                $typeId && $keyword['typeId'] = $typeId;
            } else {
                // 推荐列表直接设置顶级id为av的即可
                // 然后按照推荐级别排序
                // 注意：分类里面的福利分类是AV的父级分类，这个不能删除的，如果报错先检查分类是不是对的。
                // 2023-12-22 因为很多数据都没有父id，则用1级分类来筛选
                /*$typeId1 = TypeService::getInstance()->getAdultPidList();
                $typeId1 && $keyword['typeId1'] = $typeId1;*/
                $typeIdList = TypeService::getInstance()->getAdultIdList();
                $typeIdList && $keyword['typeId'] = $typeIdList;
            }

            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic_thumb AS vodPic',
                'vod_pic2 AS vodPic2',
            ];

            $data = VideoModel::create()
                ->order('vod_level', 'DESC')
                ->setDefaultOrder()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 获取AV分类列表
     * @Api(name="获取AV分类列表",path="/Api/Video/Adult/getTypeList")
     * @ApiDescription("获取AV分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="module", alias="模块", type="string", optional="", inArray=["Home", "Adult"], description="模块")
     * @ApiSuccess({"code":200,"result":[{"typeId":28,"typeName":"亚洲无码"},{"typeId":29,"typeName":"亚洲有码"},{"typeId":30,"typeName":"欧美情色"},{"typeId":31,"typeName":"高清中文"},{"typeId":32,"typeName":"动漫卡通"},{"typeId":33,"typeName":"美女主播"},{"typeId":34,"typeName":"传媒机构"},{"typeId":35,"typeName":"国产精品"}],"systemTimestamp":1691840765,"systemDateTime":"2023-08-12 19:46:05","msg":"success"})
     */
    public function getTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {

            $module = isset($param['module']) && $param['module'] ? $param['module'] : 'Adult';

            $redis = RedisPool::defer();
            $key = TemplateKey::prostituteTypePageCache($module);
            $result = $redis->get($key);
            if(!$result){
                $result = TypeService::getInstance()->getAdultVideoTypeList($module);
                $redis->set($key, $result, 7200);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }
    //获取热门标签-----type_sort desc
    public function getHotTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {

            $module = isset($param['module']) && $param['module'] ? $param['module'] : 'Adult';

            $redis = RedisPool::defer();
            $key = TemplateKey::prostituteTypePageCache($module);
            $result = $redis->get($key);
            if(!$result){
                $result = TypeService::getInstance()->getAdultVideoHotTypeList($module);
                $redis->set($key, $result, 7200);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }
    /**
     * 获取顶级二级分类列表
     * @Api(name="获取顶级二级分类列表",path="/Api/Video/Adult/getTopSubTypeList")
     * @ApiDescription("获取顶级二级分类列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="type", alias="类型", type="string", optional="", description="类型")
     * @ApiSuccess({"code":200,"result":[{"typeId":63,"typeName":"哈哈传媒","videoList":[{"vodId":22194,"vodName":"专属小女友00后双马尾萝莉，跳蛋振动棒齐上场，娇小身材扶细腰后入，萝莉型中的极品_0","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/ae862f6d7a0f37b7aa8692d4fef0510f.xyz","vodRemarks":""},{"vodId":22184,"vodName":"女子养生会所，胖子的春天来了给小少妇按摩，按摩棒玩逼扒光了就草，人胖鸡巴小多体位抽插，都怀疑没草进去","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/bb66c3778cf859202380d3755c051c3c.xyz","vodRemarks":""},{"vodId":22183,"vodName":"调教妹妹的老公，在一起睡觉撩醒他口交大鸡巴主动上位抽插不敢叫妹妹在旁边睡觉，太爽吵醒后连妹妹一起干","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/0c23acb4b2137c76d28068ee37715ad0.xyz","vodRemarks":""},{"vodId":22173,"vodName":"笑起来超甜美小姐姐白皙美乳圆润美臀，性感吊带黑裙开档丝袜，翘起屁股特写美穴，抬起双腿让观众看清楚","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/38853faae7b28f3c80f19d5716620f4d.xyz","vodRemarks":""},{"vodId":22172,"vodName":"调教黑丝小母狗，全程露脸无毛骚逼黑丝微SM情趣诱惑，道具玩弄口交大鸡巴，各种体位爆草抽插","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/f06390675f0279a4ecba719b8b7d6634.xyz","vodRemarks":""},{"vodId":22168,"vodName":"新晋极品女神降临，清纯校花，激情3P，模特身材明星脸，无毛白虎，啪啪暴插，刺激劲爆","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/b220bc3cd5b1ba13d7166c1c10c6c6b4.xyz","vodRemarks":""},{"vodId":22167,"vodName":"性感车模极品大秀 骚母狗一只 #直播","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/ec5d71924adb3ed667355ade55c10ad3.xyz","vodRemarks":""},{"vodId":22064,"vodName":"年轻大奶淫娃直播大黑牛刺激被草的死去活来内射","vodPic":"https://pvod.pysmowy.cn/20231226/tDDeqAf0/1.jpg","vodRemarks":""},{"vodId":22036,"vodName":"闷骚的小少妇开档丝袜高跟情趣诱惑，给大哥口交大鸡巴舔的好刺激，让大哥后入爆草抽插主动上位还拿道具玩逼_x264_aac","vodPic":"https://pvod.pysmowy.cn/20231226/ID822SGC/1.jpg","vodRemarks":""},{"vodId":22024,"vodName":"黑丝女友露脸颜值不错跟男友激情啪啪，无套抽插打桩机式爆草看着好刺激好猛，多体位射在背上玩奶子看逼特写","vodPic":"https://pvod.pysmowy.cn/20231226/mY87e8nS/1.jpg","vodRemarks":""},{"vodId":22013,"vodName":"蜜桃美臀一线天无毛极品美穴萝莉妹纸，和小男友3小时激情大战【第一篇】，镜头对着屁股骑乘打桩，扶着小腰后入一下下撞击","vodPic":"https://pvod.pysmowy.cn/20231226/Z9s9uP0N/1.jpg","vodRemarks":""},{"vodId":22006,"vodName":"妹子太清纯了全国可空降被满身胸毛男操了","vodPic":"https://pvod.pysmowy.cn/20231226/KeyT0E94/1.jpg","vodRemarks":""}]},{"typeId":64,"typeName":"小叮当传媒","videoList":[{"vodId":22194,"vodName":"专属小女友00后双马尾萝莉，跳蛋振动棒齐上场，娇小身材扶细腰后入，萝莉型中的极品_0","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/ae862f6d7a0f37b7aa8692d4fef0510f.xyz","vodRemarks":""},{"vodId":22184,"vodName":"女子养生会所，胖子的春天来了给小少妇按摩，按摩棒玩逼扒光了就草，人胖鸡巴小多体位抽插，都怀疑没草进去","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/bb66c3778cf859202380d3755c051c3c.xyz","vodRemarks":""},{"vodId":22183,"vodName":"调教妹妹的老公，在一起睡觉撩醒他口交大鸡巴主动上位抽插不敢叫妹妹在旁边睡觉，太爽吵醒后连妹妹一起干","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/0c23acb4b2137c76d28068ee37715ad0.xyz","vodRemarks":""},{"vodId":22173,"vodName":"笑起来超甜美小姐姐白皙美乳圆润美臀，性感吊带黑裙开档丝袜，翘起屁股特写美穴，抬起双腿让观众看清楚","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/38853faae7b28f3c80f19d5716620f4d.xyz","vodRemarks":""},{"vodId":22172,"vodName":"调教黑丝小母狗，全程露脸无毛骚逼黑丝微SM情趣诱惑，道具玩弄口交大鸡巴，各种体位爆草抽插","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/f06390675f0279a4ecba719b8b7d6634.xyz","vodRemarks":""},{"vodId":22168,"vodName":"新晋极品女神降临，清纯校花，激情3P，模特身材明星脸，无毛白虎，啪啪暴插，刺激劲爆","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/b220bc3cd5b1ba13d7166c1c10c6c6b4.xyz","vodRemarks":""},{"vodId":22167,"vodName":"性感车模极品大秀 骚母狗一只 #直播","vodPic":"https://xinglangtest.s3.ap-east-1.amazonaws.com/Upload/Image/video/2023/12/28/ec5d71924adb3ed667355ade55c10ad3.xyz","vodRemarks":""},{"vodId":22064,"vodName":"年轻大奶淫娃直播大黑牛刺激被草的死去活来内射","vodPic":"https://pvod.pysmowy.cn/20231226/tDDeqAf0/1.jpg","vodRemarks":""},{"vodId":22036,"vodName":"闷骚的小少妇开档丝袜高跟情趣诱惑，给大哥口交大鸡巴舔的好刺激，让大哥后入爆草抽插主动上位还拿道具玩逼_x264_aac","vodPic":"https://pvod.pysmowy.cn/20231226/ID822SGC/1.jpg","vodRemarks":""},{"vodId":22024,"vodName":"黑丝女友露脸颜值不错跟男友激情啪啪，无套抽插打桩机式爆草看着好刺激好猛，多体位射在背上玩奶子看逼特写","vodPic":"https://pvod.pysmowy.cn/20231226/mY87e8nS/1.jpg","vodRemarks":""},{"vodId":22013,"vodName":"蜜桃美臀一线天无毛极品美穴萝莉妹纸，和小男友3小时激情大战【第一篇】，镜头对着屁股骑乘打桩，扶着小腰后入一下下撞击","vodPic":"https://pvod.pysmowy.cn/20231226/Z9s9uP0N/1.jpg","vodRemarks":""},{"vodId":22006,"vodName":"妹子太清纯了全国可空降被满身胸毛男操了","vodPic":"https://pvod.pysmowy.cn/20231226/KeyT0E94/1.jpg","vodRemarks":""}]}],"systemTimestamp":1705066393,"systemDateTime":"2024-01-12 21:33:13","msg":"success"})
     */
    public function getTopSubTypeList()
    {
        $param = $this->request()->getRequestParam();
        $type_en = isset($param['type']) && $param['type'] ? $param['type'] : 'top_chuanmei';
        try {
            $result = TypeService::getInstance()->getTopSubList($type_en);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, 'success');
    }


    /**
     * 搜索热词列表
     * @Api(name="搜索热词列表",path="/Api/Video/Adult/searchKeyWords")
     * @ApiDescription("搜索热词列表。")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":["dd","aaaa"],"systemTimestamp":1698069482,"systemDateTime":"2023-10-23 21:58:02","msg":"OK"})
     */
    public function searchKeyWords()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = VideoService::getInstance()->getAdultHotWords();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}