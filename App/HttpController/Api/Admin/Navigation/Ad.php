<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\Upload;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Merchant\MerchantModel;
use App\Model\Navigation\AdGroupModel;
use App\Model\Navigation\AdModel;
use App\Service\Navigation\AdService;
use App\Service\Oss\AwsOssService;
use App\Service\Oss\LocalOssService;
use App\Utility\Func;
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
use App\HttpController\Api\Admin\Upload as uploadNew;
use Exception;
use Throwable;

/**
 * Class Ad
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-广告 Admin/Navigation/Ad")
 * @ApiGroupDescription("后台广告相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Ad extends AdminBase
{
    /**
     * 广告列表
     * @Api(name="广告列表",path="/Api/Admin/Navigation/Ad/adList")
     * @ApiDescription("广告列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="adId", alias="广告id", type="int", optional="", min="1", description="广告id")
     * @Param(name="adName", alias="广告名", type="string", optional="", mbLengthMin="1", description="广告名")
     * @Param(name="url", alias="广告链接", type="string", optional="", mbLengthMin="1", description="广告链接")
     * @Param(name="adGroupId", alias="所属广告组", type="int", optional="", min="1", description="所属广告组")
     * @Param(name="merchantName", alias="商户名字", type="string", optional="", mbLengthMin="1", description="商户名字")
     * @Param(name="remark", alias="备注", type="string", optional="", mbLengthMin="1", description="备注")
     * @Param(name="sortType", alias="排序类型", type="string", optional="", inArray=["adId_DESC", "adId_ASC", "sort_ASC", "sort_DESC"], description="1.id倒叙（adId_DESC）2.id正叙（adId_ASC）3.sort正叙（sort_ASC） 4.sort倒叙（sort_DESC）")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":1,"list":[{"adId":75,"adName":"抹茶直播3","fileType":"up","imageUrl":"/Init/Zone/22/l3.png","url":"https://www.bilibili.com","extension":{"31":{"tag":"热门","title":"在线调教","text1":"人在线","range":"888,1888","name":"xiao美M","introduction":"可爱的小姐姐\n懂事温柔 听话"}},"merchantId":1,"cost":"0.0000","remark":"","status":1,"adGroup":[{"adGroupId":31,"adGroupName":"抹茶首页直播专区","sort":30}],"merchantName":"测试商户"}],"options":{"adId":"75"}},"systemTimestamp":1704093723,"systemDateTime":"2024-01-01 15:22:03","msg":"OK"})
     */
    public function adList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['adId']) && $keyword['adId'] = $param['adId'];
            isset($param['adName']) && $keyword['adName'] = trim($param['adName']);
            isset($param['url']) && $keyword['url'] = trim($param['url']);
            isset($param['remark']) && $keyword['remark'] = trim($param['remark']);
            isset($param['adGroupId']) && $param['adGroupId'] > 0 && $keyword['adGroupId'] = $param['adGroupId'];
            isset($param['adTypeId']) && $param['adTypeId'] > 0 && $keyword['adTypeId'] = $param['adTypeId'];
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            // 2023-11-13 增加商户名的筛选字段
            if (isset($param['merchantName'])) {
                $merchant = MerchantModel::create()
                    ->where([
                        'merchantName' => trim($param['merchantName']),
                        'status' => [MerchantModel::STATE_DELETED, '!='],
                    ])
                    ->get();
                if ($merchant) {
                    $keyword['merchantId'] = $merchant->merchantId;
                }
            }

            $field = [
                'ad.adId',
                'ad.adName',
                'ad.fileType',
                'ad.imageUrl',
                'ad.url',
                'ad.extension',
                'ad.merchantId',
                'ad.cost',
                'ad.remark',
                'ad.status',
            ];

            $ad = AdModel::create();

            $sortType = $param['sortType'] ?? '';
            if (isset($keyword['adGroupId']) && $sortType) {
                $sortType = explode('_', $sortType);
                $ad->order(...$sortType);

                if ($sortType[0] == 'sort') {
                    // 如果选择的是按照sort排序，那么依然要将adId DESC作为第二排序
                    $ad->order('adId', 'DESC');
                }
            } else {
                $ad->order('adId', 'DESC');
            }

            $data = $ad->getAdAll($page, $keyword, $pageSize, $field);
            $data['list'] = AdGroupModel::create()->appendGroupInfo($data['list']);
            $data['list'] = MerchantModel::create()->appendInfo($data['list'], ['merchantName'], 'merchantId', 'merchantId');
          if($data['list']){
            foreach($data['list'] as $k=>$v){
                $imgData=new uploadNew();
                // $files=$imgData->getUrlImageAd($v["imageUrl"]);
                // $data['list'][$k]["imageUrl"]=$files["file"];
                // $data['list'][$k]["size"]=$files["size"];
                $data['list'][$k]["imageUrl"]="";
            }
          }
            
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 广告详情
     * @Api(name="广告详情",path="/Api/Admin/Navigation/Ad/adDetail")
     * @ApiDescription("广告详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="adId", alias="广告id", type="int", required="", min="1", description="广告id")
     * @ApiSuccess({"code":200,"result":{"adId":1,"adName":"测试双组","fileType":"up","imageUrl":"/Upload/Image/ad/2023/06/05/edeebc5b9dea6d83d6d5d06e73c12911.gif","url":"https://www.google.com","extension":{"10":{"title":"标题aaa","description":"数组描述bbb","descriptionRed":"数组描述ccc"}},"cost":"0.0000","status":1,"adGroup":[{"adGroupId":6,"adId":1,"sort":20},{"adGroupId":10,"adId":1,"sort":30}]},"systemTimestamp":1686384433,"systemDateTime":"2023-06-10 16:07:13","msg":"OK"})
     */
    public function adDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ad = AdModel::create()
                ->with(['adGroup'])
                ->get([
                    'adId' => $param['adId'],
                    'status' => [AdModel::STATE_DELETED, '>'],
                ]);

            $data = $ad->hidden(['createTime', 'updateTime'])->toRawArray();
            $data['adGroup'] = $ad->adGroup;
            $data['extension'] = json_decode($data['extension'], true);
            $imgData=new uploadNew();
            $data["img_show"]=$imgData->getUrlImage($data["imageUrl"]);   
            $data["transit_img_show"]=$imgData->getUrlImage($data["transit_img"]);   
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /* 广告所有的编辑相关 begin */

    /**
     * 广告添加
     * @Api(name="广告添加",path="/Api/Admin/Navigation/Ad/add")
     * @ApiDescription("广告添加<br/>
    1.所属广告组为多选，选择后需要填写对应广告组的扩展字段。<br/>
    2.上传图片先到temp<br/>
    3.adGroupId  adGroupSort  extension 均为数组形式<br/>
    请求参数举例（见成功响应示例就是请求示例）
    ")
     * @Method(allow=["POST"])
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @Param(name="adName", alias="广告名字", type="string", required="", mbLengthMin="1", description="广告名字")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="imageUrl", alias="上传图片地址", type="string", required="", description="上传图片接口所返回的临时地址")
     * @Param(name="url", alias="跳转链接", type="string", required="", mbLengthMin="1", description="跳转链接")
     * @Param(name="adGroupId", alias="所属广告组", type="array", required="", description="所属广告组，可以用数组，也可以用逗号拼接")
     * @Param(name="adGroupSort", alias="所属广告组的排序", type="array", required="", description="所属广告组的排序数组，可以用数组，也可以用json字符串")
     * @Param(name="extension", alias="所属广告组的扩展字段", type="array", optional="", description="所属广告组的扩展字段数组")
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="0", description="商户id")
     * @Param(name="cost", alias="单次点击价格", type="float", required="", min="0", description="单次点击价格")
     * @Param(name="remark", alias="备注", type="string", required="", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     * @apiSuccess({"adName": "水仙直播","fileType": "up","imageUrl": "/Upload/Image/temp/2023/06/06/3abbfab389c4e4cb0a994e8e3f8ffafa.gif","url": "https://www.google.com","adGroupId[0]": "1","adGroupId[1]": "10","extension[10][title]": "标题aaa","extension[10][description]": "数组描述bbb","extension[10][descriptionRed]": "数组描述ccc","adGroupSort[1]": "100","adGroupSort[10]": "30"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'adTypeId' => $param['adTypeId'],
                'adName' => trim($param['adName']),
                'fileType' => $param['fileType'],
                'url' => trim($param['url']),
                'merchantId' => $param['merchantId'],
                'cost' => floatval($param['cost']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
                'transit_img' => intval($param['transit_img']),//中转页图片路径
            ];

            $this->verifyAdParamStep1($param);

            /* 处理图片路径 begin */
            $this->verifyAdParamStep2($data, $param);
            /* 处理图片路径 end */

            /* 处理扩展字段 begin */
            $this->verifyAdParamStep3($data, $param);
            /* 处理扩展字段 end */

            $result = AdService::getInstance()->addAd($data, $param['adGroupId'], $param['adGroupSort']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(
            Status::CODE_OK,
            $result,
            Status::getReasonPhrase(Status::CODE_OK),
            AdminLogsModel::TYPE_ADD,
            json_encode($param, JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * 广告修改
     * @Api(name="广告修改",path="/Api/Admin/Navigation/Ad/edit")
     * @ApiDescription("广告修改，修改的参数提交格式和新增一样，只是多一个广告id")
     * @Method(allow=["POST"])
     * @Param(name="adId", alias="广告id", type="int", required="", min="1", description="广告id")
     * @Param(name="adTypeId", alias="广告分类id", type="int", required="", min="1", description="广告分类id")
     * @Param(name="adName", alias="广告名字", type="string", required="", mbLengthMin="1", description="广告名字")
     * @Param(name="fileType", alias="上传图片类型", type="string", required="", inArray=["up", "url", "awsS3"], description="上传图片类型(up本地上传,url网络地址,awsS3亚马逊s3)")
     * @Param(name="imageUrl", alias="上传图片地址", type="string", required="", description="上传图片接口所返回的临时地址")
     * @Param(name="url", alias="跳转链接", type="string", required="", mbLengthMin="1", description="跳转链接")
     * @Param(name="adGroupId", alias="所属广告组", type="array", required="", description="所属广告组，可以用数组，也可以用逗号拼接")
     * @Param(name="adGroupSort", alias="所属广告组的排序", type="array", required="", description="所属广告组的排序数组，可以用数组，也可以用json字符串")
     * @Param(name="extension", alias="所属广告组的扩展字段", type="array", optional="", description="所属广告组的扩展字段数组")
     * @Param(name="merchantId", alias="商户id", type="int", required="", min="0", description="商户id")
     * @Param(name="cost", alias="单次点击价格", type="float", required="", min="0", description="单次点击价格")
     * @Param(name="remark", alias="备注", type="string", required="", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'adId' => $param['adId'],
                'adTypeId' => $param['adTypeId'],
                'adName' => trim($param['adName']),
                'fileType' => $param['fileType'],
                'url' => trim($param['url']),
                'merchantId' => $param['merchantId'],
                'cost' => floatval($param['cost']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
                'transit_img' => intval($param['transit_img']),//中转页图片路径
            ];

            $this->verifyAdParamStep1($param);

            // 这里获取的是当前数据，用作对比判断。
            $ad = AdModel::create()->get($data['adId']);
            if (!$ad) {
                throw new Exception('无效的广告id', Status::CODE_BAD_REQUEST);
            }

            /* 处理图片路径 begin */
            if ($ad['fileType'] != $param['fileType'] || $ad['imageUrl'] != $param['imageUrl']) {
                $this->verifyAdParamStep2($data, $param);
            }
            /* 处理图片路径 end */

            /* 处理扩展字段 begin */
            $this->verifyAdParamStep3($data, $param);
            /* 处理扩展字段 end */

            $result = AdService::getInstance()->editAd($data, $param['adGroupId'], $param['adGroupSort']);

            // 最后要删除之前的老图片（如果有修改图片的话）
            if ($ad['fileType'] != $param['fileType'] || $ad['imageUrl'] != $param['imageUrl']) {
                // 是上传，且不是测试数据则需要把图片删了。
                if ($ad['isTest'] != AdModel::TEST_YES) {
                    switch ($ad['fileType']) {
                        case AdModel::FILE_TYPE_UP:
                            LocalOssService::getInstance()->deleteObject($ad['imageUrl']);
                            break;
                        case AdModel::FILE_TYPE_AWS_S3:
                            AwsOssService::getInstance()->deleteObject($ad['imageUrl']);
                            break;
                    }
                }
            }

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

    private function verifyAdParamStep1(&$param)
    {
        is_string($param['adGroupId']) && $param['adGroupId'] = explode(',', $param['adGroupId']);
        if (count($param['adGroupId']) < 1) {
            throw new Exception('未选择所属广告组', Status::CODE_BAD_REQUEST);
        }

        is_string($param['adGroupSort']) && $param['adGroupSort'] = json_decode($param['adGroupSort'], true);
        foreach ($param['adGroupId'] as $item) {
            if (!isset($param['adGroupSort'][$item])) {
                throw new Exception('所属广告组与所属广告组排序参数不一致', Status::CODE_BAD_REQUEST);
            }
        }
    }

    private function verifyAdParamStep2(&$data, $param)
    {
        switch ($param['fileType']) {
            case AdModel::FILE_TYPE_UP:
                // 如果是上传的文件需要将临时文件转到广告目录
                $imgUrlArr = explode('.',$param['imageUrl']);
                $imgUrl = $imgUrlArr[0];
                $data['imageUrl'] = $imgUrl . '.jpg';
                $data['imageUrl2'] = $param['imageUrl'];
                //$data['imageUrl'] = Func::moveTempFile($param['imageUrl'], Upload::TYPE_AD);
                break;
            case AdModel::FILE_TYPE_AWS_S3:
                // 亚马逊S3也是使用的相对路径，不需要处理本地图片。
            case AdModel::FILE_TYPE_URL:
                $data['imageUrl'] = $param['imageUrl'];
                break;
        }
    }

    private function verifyAdParamStep3(&$data, &$param)
    {
        // 如果选择了有扩展字段的广告组，则需要验证广告组对应的扩展字段是否存在。
        if (isset($param['extension']) && $param['extension'] && is_string($param['extension'])) {
            $param['extension'] = json_decode($param['extension'], JSON_UNESCAPED_UNICODE);
        }

        $extension = [];
        foreach ($param['adGroupId'] as $adGroupIdItem) {
            $extensionFields = AdGroupModel::create()->getExtensionFields($adGroupIdItem);
            foreach ($extensionFields as $extensionField) {
                $keyItem = $extensionField['key'];
                $extension[$adGroupIdItem][$keyItem] = $param['extension'][$adGroupIdItem][$keyItem] ?? '';
            }
        }

        $data['extension'] = json_encode($extension, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 广告修改状态
     * @Api(name="广告修改状态",path="/Api/Admin/Navigation/Ad/setStatus")
     * @ApiDescription("广告修改状态")
     * @Method(allow=["POST"])
     * @Param(name="adId", alias="广告id", type="int", required="", min="1", description="广告id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ad = AdModel::create()->where(['status' => [AdModel::STATE_DELETED, '>']])->get($param['adId']);

            if (!$ad) {
                throw new Exception('无效的广告id', Status::CODE_BAD_REQUEST);
            }

            $result = $ad->update(['status' => intval($param['status'])]);

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
     * 广告删除
     * @Api(name="广告删除",path="/Api/Admin/Navigation/Ad/delete")
     * @ApiDescription("广告删除")
     * @Method(allow=["POST"])
     * @Param(name="adId", alias="广告id", type="int", required="", min="1", description="广告id")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $ad = AdModel::create()->where(['status' => [AdModel::STATE_DELETED, '>']])->get($param['adId']);

            if (!$ad) {
                throw new Exception('无效的广告id', Status::CODE_BAD_REQUEST);
            }

            // 目前是仅修改状态，然后删除上传的文件。以保留点击统计表中的关联数据。
            $result = $ad->update(['status' => AdModel::STATE_DELETED]);

            // 是上传，且不是测试数据则需要把图片删了。
            if ($ad['isTest'] != AdModel::TEST_YES) {
                switch ($ad['fileType']) {
                    case AdModel::FILE_TYPE_UP:
                        LocalOssService::getInstance()->deleteObject($ad['imageUrl']);
                        break;
                    case AdModel::FILE_TYPE_AWS_S3:
                        AwsOssService::getInstance()->deleteObject($ad['imageUrl']);
                        break;
                }
            }

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
    /* 广告所有的编辑相关 end */

    /**
     * 广告批量修改
     * @Api(name="广告批量修改",path="/Api/Admin/Navigation/Ad/batchEdit")
     * @ApiDescription("广告批量修改")
     * @Method(allow=["POST"])
     * @Param(name="adId", alias="广告id", type="string", required="", mbLengthMin="1", description="广告id，多个用逗号连接")
     * @Param(name="url", alias="跳转链接", type="string", description="跳转链接")
     * @Param(name="adName", alias="广告名称", type="string", description="广告名称")
     * @Param(name="remark", alias="备注", type="string", description="备注")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function batchEdit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $adIdList = explode(',', $param['adId']);
            $data = [];
            $url = trim($param['url']);
            $adName = trim($param['adName']);
            $remark = trim($param['remark']);

            !empty($url) && $data['url'] = $url;
            !empty($adName) && $data['adName'] = $adName;
            !empty($remark) && $data['remark'] = $remark;

            $result = AdService::getInstance()->batchEditAd($adIdList, $data);

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
}