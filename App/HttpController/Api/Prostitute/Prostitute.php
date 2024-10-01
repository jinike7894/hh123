<?php

namespace App\HttpController\Api\Prostitute;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\User\UserBase;
use App\Model\Prostitute\ProstituteModel;
use App\Model\Prostitute\ProstituteTypeModel;
use App\Model\Region\CityModel;
use App\Model\Region\ProvinceModel;
use App\RedisKey\Navigation\TemplateKey;
use App\Service\Prostitute\ProstituteService;
use EasySwoole\EasySwoole\Core;
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
use EasySwoole\RedisPool\RedisPool;
use Exception;
use Throwable;

/**
 * Class Prostitute
 * @package App\HttpController\Api\Prostitute
 * @ApiGroup(groupName="楼凤 Prostitute/Prostitute")
 * @ApiGroupDescription("楼凤相关的操作")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Prostitute extends UserBase
{
    /**
     * 楼凤列表
     * @Api(name="楼凤列表",path="/Api/Prostitute/Prostitute/prostituteList")
     * @ApiDescription("楼凤列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @Param(name="prostituteTypeKey", alias="楼凤分类key", type="string", optional="", mbLengthMin="1", description="楼凤分类key")
     * @Param(name="title", alias="标题", type="string", optional="", mbLengthMin="1", description="标题")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["sort_DESC", "prostituteId_DESC"], description="1.sort 智能排序 2.prostituteId按发布时间")
     * @ApiSuccess({"code":200,"result":{"total":2,"list":[{"prostituteId":2,"title":"02年学妹高潮会喷粉嫩会夹","content":"妹妹长得乖巧可爱，有点羞涩，刚破处不久，还是不太会，但是服务很好啊，听话，你说什么都会积极配合，说是为了熟练一下服务各位狼友，下面很紧很嫩，奶子像小樱桃一样哈哈，推荐一下，钱花的很值得，各位狼友快来试试吧。","extension":{"number":"1","age":"02年","service":"做爱","duration":"自己问","cost":"800，1400","avatar":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/ava2.png","fileType":"url","author":"苏玲"},"prostitutePictureRelation":[{"prostitutePicId":5,"prostituteId":2,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2_1.jpg","sort":0},{"prostitutePicId":6,"prostituteId":2,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2_2.jpg","sort":0},{"prostitutePicId":7,"prostituteId":2,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2_3.jpg","sort":0},{"prostitutePicId":8,"prostituteId":2,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2_4.jpg","sort":0}]}],"options":{"status":1,"prostituteTypeId":1}},"systemTimestamp":1700224329,"systemDateTime":"2023-11-17 20:32:09","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"prostituteId":6,"title":"高端极品大奶少妇兼职外围","content":"赔付安全有保障，绝对是在校学生妹兼职，皮肤白嫩，服务态度好，服务到位，如果服务不喜欢，不满意，可以包退包换，高端外围服务，也可以介绍朋友过来有提成，诚信经营。","extension":{"costP":"700","cost2P":"1200","costN":"1800","service":"制服诱惑，口爱，臀推，情趣内衣，胸推，鸳鸯浴，舌吻，69，SM","age":"21","height":"164cm"},"prostitutePictureRelation":[{"prostitutePicId":17,"prostituteId":6,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/d1.jpg","sort":0},{"prostitutePicId":18,"prostituteId":6,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/d2.jpg","sort":0}]}],"options":{"status":1,"prostituteTypeId":2}},"systemTimestamp":1700218130,"systemDateTime":"2023-11-17 18:48:50","msg":"OK"})
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"prostituteId":8,"title":"河北女孩，找实力富豪，身高172大长腿性感漂亮","content":"如题，仅限河北地区，不去外地。","extension":{"gender":"女","cost":"面议","age":"21","height":"172cm"},"prostitutePictureRelation":[{"prostitutePicId":21,"prostituteId":8,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/b1.jpg","sort":0},{"prostitutePicId":22,"prostituteId":8,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/b2.jpg","sort":0},{"prostitutePicId":23,"prostituteId":8,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/b3.jpg","sort":0}]}],"options":{"status":1,"prostituteTypeId":3}},"systemTimestamp":1700218144,"systemDateTime":"2023-11-17 18:49:04","msg":"OK"})
     */
    public function prostituteList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $keyword['status'] = ProstituteModel::STATE_NORMAL;
            isset($param['prostituteTypeId']) && $keyword['prostituteTypeId'] = intval($param['prostituteTypeId']);
            isset($param['title']) && $keyword['title'] = trim($param['title']);

            if (isset($param['prostituteTypeKey']) && $param['prostituteTypeKey']) {
                $keyword['prostituteTypeId'] = ProstituteTypeModel::create()
                    ->where(['typeKey' => $param['prostituteTypeKey']])
                    ->val('prostituteTypeId');
            }

            $field = [
                'prostituteId',
                'title',
                'content',
                'extension',
                'provinceId',
                'cityId',
            ];

            $sortType = $param['sortType'] ?? 'sort_DESC';

            $redis = RedisPool::defer();
            $key = TemplateKey::prostitutePageCache($param['prostituteTypeId'].'-'.$page);
            $data = $redis->get($key);
            if(!$data){
                $data = ProstituteModel::create()
                    ->with(['prostitutePictureRelation'])
                    ->setOrderType($sortType)
                    ->setDefaultOrder()
                    ->getAll($page, $keyword, $pageSize, $field);

                $collection = [];
                foreach ($data['list'] as $item) {
                    $temp = $item->toRawArray();
                    $temp['extension'] = json_decode($temp['extension'], true);

                    // 因为toRawArray所以要把关联数据补上
                    $temp['prostitutePictureRelation'] = $item['prostitutePictureRelation'];

                    $collection[] = $temp;
                }
                $data['list'] = $collection;

                $data['list'] = ProvinceModel::create()->appendInfo($data['list'], ['provinceName'], 'provinceId', 'provinceId');
                $data['list'] = CityModel::create()->appendInfo($data['list'], ['cityName'], 'cityId', 'cityId');
                $redis->set($key, $data, 7200);
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤详情
     * @Api(name="楼凤详情",path="/Api/Prostitute/Prostitute/prostituteDetail")
     * @ApiDescription("楼凤详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @ApiSuccess({"code":200,"result":{"prostituteId":1,"prostituteTypeId":1,"title":"成都超会玩的制服小妹 胸大而且超级欲女！","content":"小姐姐是我一个朋友介绍的，据说很不错，抱着试一试的心态就去了。小区很好找，小姐姐遥控上楼。一进门就看见她穿着情趣制服，黑丝高跟，两个奶子一半都在外面，一下子就硬了。小姐姐会让你先洗澡，然后给你擦身子，擦到前面，就会含住一顿猛吸。接着让你坐在床上，进行ab面的服务，她两个大奶子又大又软，摸着贼舒服。做完服务，直入主题。完事儿小姐姐还会陪你聊天。整体来说很不错，小姐姐不催钟，很热情。","type":"Ad","address":"**********","contact":"**********","provinceId":1,"cityId":1,"extension":{"number":"1","age":"22","service":"制服、舌吻","duration":"1小时","cost":"600全套","avatar":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/ava1.png","fileType":"url","author":"艾楠"},"sort":1,"status":1,"createTime":"2023-11-17 16:00:16","updateTime":"2023-11-28 22:00:21","prostitutePictureRelation":[{"prostitutePicId":3,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/3.jpg","sort":10},{"prostitutePicId":4,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/4.jpg","sort":4},{"prostitutePicId":2,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2.jpg","sort":2},{"prostitutePicId":1,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/1.jpg","sort":1}],"provinceRelation":{"provinceId":1,"provinceName":"北京市"},"cityRelation":{"cityId":1,"cityName":"北京市","zipCode":"100000","provinceId":1},"prostituteType":{"prostituteTypeId":1,"title":"楼凤信息","typeKey":"Information","relatedAdId":"13","sort":30,"status":1}},"systemTimestamp":1701239296,"systemDateTime":"2023-11-29 14:28:16","msg":"OK"})
     */
    public function prostituteDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            $keyword['status'] = ProstituteModel::STATE_NORMAL;
            isset($param['prostituteId']) && $keyword['prostituteId'] = intval($param['prostituteId']);

            $data = ProstituteService::getInstance()->getDetail($keyword);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤的联系方式点击
     * @Api(name="楼凤的联系方式点击",path="/Api/Prostitute/Prostitute/prostituteClick")
     * @ApiDescription("楼凤的联系方式点击")
     * @Method(allow=["POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1700657531,"systemDateTime":"2023-11-22 20:52:11","msg":"OK"})
     */
    public function prostituteClick()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ip = $this->clientRealIP();

            // 这个是测试用的
            if (Core::getInstance()->runMode() == 'dev' && isset($param['ip'])) {
                $ip = $param['ip'];
            }

            $data = ProstituteService::getInstance()->click([
                'prostituteId' => intval($param['prostituteId']),
                'ip' => $ip,
                'deviceId' => $this->who['deviceId'],
            ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}