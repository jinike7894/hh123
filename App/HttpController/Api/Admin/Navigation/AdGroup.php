<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\HttpController\Api\Admin\AdminBase;
use App\Model\Navigation\AdGroupModel;
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

/**
 * Class AdGroup
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-广告组 Admin/Navigation/AdGroup")
 * @ApiGroupDescription("后台广告组相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class AdGroup extends AdminBase
{
    /**
     * 获取广告组列表
     * @Api(name="获取广告组列表",path="/Api/Admin/Navigation/AdGroup/groupList")
     * @ApiDescription("获取广告组列表")
     * @Method(allow=["GET", "POST"])
     * @apiSuccess({"code":200,"result":[{"adGroupId":1,"adGroupName":"顶部浮动","adGroupAlias":"","extensionFields":[]},{"adGroupId":2,"adGroupName":"横幅","adGroupAlias":"","extensionFields":[]},{"adGroupId":3,"adGroupName":"tab热门","adGroupAlias":"热门","extensionFields":[]},{"adGroupId":4,"adGroupName":"tab视频","adGroupAlias":"视频","extensionFields":[]},{"adGroupId":5,"adGroupName":"tab直播","adGroupAlias":"直播","extensionFields":[]},{"adGroupId":6,"adGroupName":"tab游戏","adGroupAlias":"游戏","extensionFields":[]},{"adGroupId":7,"adGroupName":"推荐","adGroupAlias":"下载推荐","extensionFields":[{"name":"标签","key":"tag"},{"name":"下载次数","key":"times"}]},{"adGroupId":8,"adGroupName":"约会","adGroupAlias":"","extensionFields":[{"name":"标签1","key":"tag1"},{"name":"标签2","key":"tag2"},{"name":"标签3","key":"tag3"},{"name":"年龄","key":"age"},{"name":"身高","key":"height"},{"name":"尺寸","key":"cup"},{"name":"地区","key":"district"}]},{"adGroupId":9,"adGroupName":"底部浮动","adGroupAlias":"","extensionFields":[{"name":"描述","key":"description"}]},{"adGroupId":10,"adGroupName":"tab赚钱","adGroupAlias":"赚钱","extensionFields":[{"name":"右下脚标题","key":"title"},{"name":"描述","key":"description"},{"name":"描述（红字）","key":"descriptionRed"}]}],"systemTimestamp":1685947774,"systemDateTime":"2023-06-05 14:49:34","msg":"OK"})
     */
    public function groupList()
    {
        try {
            // 2023-11-19 加一个状态筛选，免得太多了，因为广告组是定义的，最好别删，禁用就不会在后台下拉框显示，不影响数据关联。
            $keyword = ['status' => AdGroupModel::STATE_NORMAL];
            $data = AdGroupModel::create()->getList($keyword);

        } catch (\Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
}