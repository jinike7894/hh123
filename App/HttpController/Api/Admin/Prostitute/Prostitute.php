<?php

namespace App\HttpController\Api\Admin\Prostitute;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Prostitute\ProstituteClickModel;
use App\Model\Prostitute\ProstituteModel;
use App\Model\Region\CityModel;
use App\Model\Region\ProvinceModel;
use App\Service\Prostitute\ProstituteService;
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
 * Class Prostitute
 * @package App\HttpController\Api\Admin\Prostitute
 * @ApiGroup(groupName="后台-楼凤-楼凤 Admin/Prostitute/Prostitute")
 * @ApiGroupDescription("后台楼凤模块楼凤相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Prostitute extends AdminBase
{
    /**
     * 楼凤列表
     * @Api(name="楼凤列表",path="/Api/Admin/Prostitute/Prostitute/prostituteList")
     * @ApiDescription("楼凤列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="prostituteId", alias="楼凤id", type="int", optional="", min="1", description="楼凤id")
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", optional="", min="1", description="楼凤分类id")
     * @Param(name="title", alias="标题", type="string", optional="", mbLengthMin="1", description="标题")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["prostituteId_DESC", "prostituteId_ASC", "sort_DESC", "sort_ASC"], description="1.id倒叙（prostituteId_DESC）2.id正叙（prostituteId_ASC）3.sort倒叙（sort_DESC）4.sort正叙（sort_ASC）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"prostituteId":1,"prostituteTypeId":1,"title":"成都超会玩的制服小妹 胸大而且超级欲女！","provinceId":1,"cityId":1,"sort":1,"status":1,"createTime":"2023-11-17 16:00:16","prostitutePictureRelation":[{"prostitutePicId":3,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/3.jpg","sort":10},{"prostitutePicId":4,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/4.jpg","sort":4},{"prostitutePicId":2,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2.jpg","sort":2},{"prostitutePicId":1,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/1.jpg","sort":1}],"provinceName":"北京市","cityName":"北京市"}],"options":{"prostituteId":"1"}},"systemTimestamp":1701180148,"systemDateTime":"2023-11-28 22:02:28","msg":"OK"})
     */
    public function prostituteList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['prostituteId']) && $keyword['prostituteId'] = $param['prostituteId'];
            isset($param['prostituteTypeId']) && $keyword['prostituteTypeId'] = $param['prostituteTypeId'];
            isset($param['title']) && $keyword['title'] = trim($param['title']);
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'prostituteId',
                'prostituteTypeId',
                'title',
                'provinceId',
                'cityId',
                'sort',
                'status',
                'createTime',
            ];

            $sortType = $param['sortType'] ?? '';

            $data = ProstituteModel::create()
                ->with(['prostitutePictureRelation'])
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = ProvinceModel::create()->appendInfo($data['list'], ['provinceName'], 'provinceId', 'provinceId');
            $data['list'] = CityModel::create()->appendInfo($data['list'], ['cityName'], 'cityId', 'cityId');

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤详情
     * @Api(name="楼凤详情",path="/Api/Admin/Prostitute/Prostitute/prostituteDetail")
     * @ApiDescription("楼凤详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @ApiSuccess({"code":200,"result":{"prostituteId":1,"prostituteTypeId":1,"title":"成都超会玩的制服小妹 胸大而且超级欲女！","content":"小姐姐是我一个朋友介绍的，据说很不错，抱着试一试的心态就去了。小区很好找，小姐姐遥控上楼。一进门就看见她穿着情趣制服，黑丝高跟，两个奶子一半都在外面，一下子就硬了。小姐姐会让你先洗澡，然后给你擦身子，擦到前面，就会含住一顿猛吸。接着让你坐在床上，进行ab面的服务，她两个大奶子又大又软，摸着贼舒服。做完服务，直入主题。完事儿小姐姐还会陪你聊天。整体来说很不错，小姐姐不催钟，很热情。","address":"**********","contact":"**********","extension":{"number":"1","age":"22","service":"制服、舌吻","duration":"1小时","cost":"600全套","avatar":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/ava1.png","fileType":"url","author":"艾楠"},"sort":1,"status":1,"createTime":"2023-11-17 16:00:16","updateTime":"2023-11-17 21:58:20","prostitutePictureRelation":[{"prostitutePicId":3,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/3.jpg","sort":10},{"prostitutePicId":4,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/4.jpg","sort":4},{"prostitutePicId":2,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/2.jpg","sort":2},{"prostitutePicId":1,"prostituteId":1,"fileType":"url","url":"https://mocha-video.s3.ap-east-1.amazonaws.com/loufengTest/1.jpg","sort":1}]},"systemTimestamp":1700288656,"systemDateTime":"2023-11-18 14:24:16","msg":"OK"})
     */
    public function prostituteDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];

            isset($param['prostituteId']) && $keyword['prostituteId'] = intval($param['prostituteId']);

            $data = ProstituteService::getInstance()->getDetail($keyword, false, false);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤添加
     * @Api(name="楼凤添加",path="/Api/Admin/Prostitute/Prostitute/add")
     * @ApiDescription("楼凤添加")
     * @Method(allow=["POST"])
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", required="", min="1", description="楼凤分类id")
     * @Param(name="title", alias="标题", type="string", required="", mbLengthMin="1", mbLengthMax="64", description="标题")
     * @Param(name="content", alias="内容", type="string", required="", mbLengthMin="0", mbLengthMax="500", description="内容")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="pictureList", alias="图片数组", type="string", required="", description="图片数组")
     * @Param(name="pictureSort", alias="图片排序数组", type="string", required="", description="图片排序数组")
     * @Param(name="type", alias="数据类型", type="string", required="", inArray=["Real", "Ad"], description="数据类型(Real 真实的,Ad 广告)")
     * @Param(name="address", alias="地址", type="string", required="", mbLengthMin="0", description="地址")
     * @Param(name="contact", alias="联系方式", type="string", required="", mbLengthMin="0", description="联系方式")
     * @Param(name="extension", alias="扩展字段的数组", type="string", required="", description="扩展字段的数组")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @Param(name="provinceId", alias="省级id", type="int", required="", min="1", description="省级id")
     * @Param(name="cityId", alias="市级id", type="int", required="", min="1", description="市级id")
     * @ApiSuccess({"code":200,"result":1,"systemTimestamp":1700294394,"systemDateTime":"2023-11-18 15:59:54","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteTypeId' => intval($param['prostituteTypeId']),
                'title' => trim($param['title']),
                'content' => trim($param['content']),
                'fileType' => trim($param['fileType']),
                'pictureList' => json_decode(trim($param['pictureList']), true),
                'pictureSort' => json_decode(trim($param['pictureSort']), true),
                'type' => trim($param['type']),
                'address' => trim($param['address']),
                'contact' => trim($param['contact']),
                'extension' => json_decode(trim($param['extension']), true),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'provinceId' => intval($param['provinceId']),
                'cityId' => intval($param['cityId']),
            ];

            $data['extension'] = json_encode($data['extension'], JSON_UNESCAPED_UNICODE);
            $result = ProstituteService::getInstance()->addProstitute($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 楼凤编辑
     * @Api(name="楼凤编辑",path="/Api/Admin/Prostitute/Prostitute/edit")
     * @ApiDescription("楼凤编辑")
     * @Method(allow=["POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @Param(name="prostituteTypeId", alias="楼凤分类id", type="int", required="", min="1", description="楼凤分类id")
     * @Param(name="title", alias="标题", type="string", required="", mbLengthMin="1", mbLengthMax="64", description="标题")
     * @Param(name="content", alias="内容", type="string", required="", mbLengthMin="0", mbLengthMax="500", description="内容")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="pictureList", alias="图片数组", type="string", required="", description="图片数组")
     * @Param(name="pictureSort", alias="图片排序数组", type="string", required="", description="图片排序数组")
     * @Param(name="type", alias="数据类型", type="string", required="", inArray=["Real", "Ad"], description="数据类型(Real 真实的,Ad 广告)")
     * @Param(name="address", alias="地址", type="string", required="", mbLengthMin="0", description="地址")
     * @Param(name="contact", alias="联系方式", type="string", required="", mbLengthMin="0", description="联系方式")
     * @Param(name="extension", alias="扩展字段的数组", type="string", required="", description="扩展字段的数组")
     * @Param(name="sort", alias="排序", type="int", required="", min="0", max="65535", description="排序")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @Param(name="provinceId", alias="省级id", type="int", required="", min="1", description="省级id")
     * @Param(name="cityId", alias="市级id", type="int", required="", min="1", description="市级id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1700294550,"systemDateTime":"2023-11-18 16:02:30","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteId' => intval($param['prostituteId']),
                'prostituteTypeId' => intval($param['prostituteTypeId']),
                'title' => trim($param['title']),
                'content' => trim($param['content']),
                'fileType' => trim($param['fileType']),
                'pictureList' => json_decode(trim($param['pictureList']), true),
                'pictureSort' => json_decode(trim($param['pictureSort']), true),
                'type' => trim($param['type']),
                'address' => trim($param['address']),
                'contact' => trim($param['contact']),
                'extension' => json_decode(trim($param['extension']), true),
                'sort' => intval($param['sort']),
                'status' => intval($param['status']),
                'provinceId' => intval($param['provinceId']),
                'cityId' => intval($param['cityId']),
            ];

            $data['extension'] = json_encode($data['extension'], JSON_UNESCAPED_UNICODE);
            $result = ProstituteService::getInstance()->editProstitute($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 楼凤联系方式批量修改
     * @Api(name="楼凤联系方式批量修改",path="/Api/Admin/Prostitute/Prostitute/batchEdit")
     * @ApiDescription("楼凤联系方式批量修改")
     * @Method(allow=["POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @Param(name="contact", alias="联系方式", type="string", required="", mbLengthMin="0", description="联系方式")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function batchEdit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $prostituteIdList = explode(',', $param['prostituteId']);

            $data = [
                'type' => 'Real',
                'contact' => trim($param['contact']),
            ];

            $result = ProstituteService::getInstance()->batchEditAd($prostituteIdList, $data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 楼凤修改状态
     * @Api(name="楼凤修改状态",path="/Api/Admin/Prostitute/Prostitute/setStatus")
     * @ApiDescription("楼凤修改状态")
     * @Method(allow=["POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1700294919,"systemDateTime":"2023-11-18 16:08:39","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteId' => intval($param['prostituteId']),
                'status' => intval($param['status']),
            ];

            $result = ProstituteService::getInstance()->editProstitute($data, false);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_UPDATE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 楼凤删除
     * @Api(name="楼凤删除",path="/Api/Admin/Prostitute/Prostitute/delete")
     * @ApiDescription("楼凤删除")
     * @Method(allow=["POST"])
     * @Param(name="prostituteId", alias="楼凤id", type="int", required="", min="1", description="楼凤id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1700294937,"systemDateTime":"2023-11-18 16:08:57","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'prostituteId' => intval($param['prostituteId']),
                'status' => ProstituteModel::STATE_DELETED,
            ];

            // 会同步删除楼凤的图片
            $result = ProstituteService::getInstance()->editProstitute($data);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_DELETE,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 楼凤数据类型列表
     * @Api(name="楼凤数据类型列表",path="/Api/Admin/Prostitute/Prostitute/getDataTypeList")
     * @ApiDescription("楼凤数据类型列表")
     * @Method(allow=["GET", "POST"])
     * @ApiSuccess({"code":200,"result":[{"key":"Real","name":"真实"},{"key":"Ad","name":"广告"}],"systemTimestamp":1700554903,"systemDateTime":"2023-11-21 16:21:43","msg":"OK"})
     */
    public function getDataTypeList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = ProstituteModel::TYPE_LIST_TEXT;

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 楼凤真实信息查看点击报表
     * @Api(name="楼凤真实信息查看点击报表",path="/Api/Admin/Prostitute/Prostitute/prostituteClickList")
     * @ApiDescription("楼凤真实信息查看点击报表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="dateStart", alias="开始时间", type="string", optional="", description="开始日期(2022-01-01) 无时分秒，不筛选则不传，可以只传开始时间。")
     * @Param(name="dateEnd", alias="结束时间", type="string", optional="", description="结束日期(2022-01-01) 无时分秒，不筛选则不传，可以只传结束时间。")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["date_DESC", "date_ASC", "clickCount_DESC", "clickCount_ASC"], description="1.日期（date）2.点击数（点击数）")
     * @Param(name="export", alias="是否导出", type="int", optional="", inArray=[1,0], description="1.是 0.否")
     * @ApiSuccess({"code":200,"result":{"total":5,"list":[{"date":"2023-11-24","prostituteId":5,"contact":"微信：xy666","clickCount":5,"title":"高端极品风骚眼镜妹兼职"},{"date":"2023-11-22","prostituteId":8,"contact":"微信：xy123","clickCount":4,"title":"tt1"},{"date":"2023-11-22","prostituteId":7,"contact":"微信：xy777","clickCount":2,"title":"杭州在读学生，想找包月资助"},{"date":"2023-11-22","prostituteId":6,"contact":"微信：xy666","clickCount":1,"title":"高端极品大奶少妇兼职外围"},{"date":"2023-11-22","prostituteId":5,"contact":"微信：xy555","clickCount":1,"title":"高端极品风骚眼镜妹兼职"}],"options":[],"sum":{"clickCount":"13"}},"systemTimestamp":1700806640,"systemDateTime":"2023-11-24 14:17:20","msg":"OK"})
     */
    public function prostituteClickList()
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

            isset($param['dateStart']) && $clickKeyword['dateStart'] = $keyword['dateStart'] = date('Y-m-d', strtotime($param['dateStart']));
            isset($param['dateEnd']) && $clickKeyword['dateEnd'] = $keyword['dateEnd'] = date('Y-m-d', strtotime($param['dateEnd']));

            $field = [
                'date',
                'prostituteId',
                'contact',
                'clickCount',
            ];

            $sortType = $param['sortType'] ?? 'date_DESC';

            $prostituteClick = ProstituteClickModel::create();
            $data = $prostituteClick
                ->setOrderType($sortType)
                ->getAll($page, $keyword, $pageSize, $field);

            $data['list'] = ProstituteModel::create()->appendInfo($data['list'], ['title'], 'prostituteId', 'prostituteId');

            if ($param['export']) {
                if (!$data['list']) {
                    throw new \Exception('没有需要下载的数据', Status::CODE_BAD_REQUEST);
                }

                $fileName = '楼凤真实信息查看点击报表.xlsx';
                $headers = [
                    ['日期', 'date'],
                    ['楼凤id', 'prostituteId'],
                    ['联系方式', 'contact'],
                    ['点击次数', 'clickCount'],
                ];
                $this->downloadExcel($headers, $data['list'], $fileName);
            } else {
                $data['sum'] = $prostituteClick::create()->getSum($keyword);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}