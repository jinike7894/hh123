<?php

namespace App\HttpController\Api\Admin\Navigation;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Navigation\PageModel;
use App\Model\Navigation\PageTemplateModel;
use App\Service\Navigation\PageService;
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
use EasySwoole\ORM\Collection\Collection;
use Exception;
use Throwable;

/**
 * Class Page
 * @package App\HttpController\Api\Admin\Navigation
 * @ApiGroup(groupName="后台-导航-页面 Admin/Navigation/Page")
 * @ApiGroupDescription("后台广告相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Page extends AdminBase
{

    /**
     * 页面列表
     * @Api(name="页面列表",path="/Api/Admin/Navigation/Page/pageList")
     * @ApiDescription("页面列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @ApiSuccess({"code":200,"result":{"total":10,"list":[{"pageId":2,"pageName":"test.html","pageTemplateId":7,"code":"<script>console.log('test')</script>","description":"测试页面","statisticEnabled":0,"statisticConfig":"","ipCost":"0.00","latestTime":"2023-10-27 14:43:37","status":1,"pageTemplateName":"抹茶","isExists":1},{"pageId":1,"pageName":"index.html","pageTemplateId":1,"code":"<script>console.log('index')</script>","description":"默认页面请勿删除。","statisticEnabled":0,"statisticConfig":"","ipCost":"0.00","latestTime":"2023-10-27 14:43:54","status":1,"pageTemplateName":"默认","isExists":1}],"options":[]},"systemTimestamp":1699859175,"systemDateTime":"2023-11-13 15:06:15","msg":"OK"})
     */
    public function pageList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            $field = [
                'p.pageId',
                'p.pageName',
                'p.pageTemplateId',
                'pt.pageTemplateName',
                'p.code',
                'p.navCode',
                'p.description',
                'p.statisticEnabled',
                'p.statisticConfig',
                'p.ipCost',
                'p.latestTime',
                'p.status',
            ];

            $data = PageModel::create()
                ->alias('p')
                ->join(PageTemplateModel::create()->getTableName() . ' AS pt', 'p.pageTemplateId = pt.pageTemplateId', 'LEFT')
                ->order('pageId', 'DESC')
                ->getAll($page, $keyword, $pageSize, $field);

            foreach ($data['list'] as $datum) {
                /**
                 * @var Collection $datum
                 */
                $filePath = SystemConfigKey::FRONTEND_PATH . '/' . $datum['pageName'];
                $datum->isExists = file_exists($filePath) ? 1 : 0;
                $datum->append(['isExists']);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 全页面关联列表
     * @Api(name="全页面关联列表",path="/Api/Admin/Navigation/Page/pageListAll")
     * @ApiDescription("全页面关联列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageName", alias="页面名字", type="string", optional="", mbLengthMin="1", description="页面名字的搜索词，不带就是全部获取。")
     * @ApiSuccess({"code":200,"result":[{"pageId":2,"pageName":"test.html"},{"pageId":1,"pageName":"index.html"}],"systemTimestamp":1688095289,"systemDateTime":"2023-06-30 11:21:29","msg":"OK"})
     */
    public function pageListAll()
    {
        $param = $this->request()->getRequestParam();

        try {
            $page = PageModel::create();

            if (isset($param['pageName'])) {
                $page->where(['pageName' => [$param['pageName'] . '%', 'LIKE']]);
            }

            $data = $page
                ->field(['pageId', 'pageName'])
                ->setDefaultOrder()
                ->all();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 页面详情
     * @Api(name="页面详情",path="/Api/Admin/Navigation/Page/pageDetail")
     * @ApiDescription("页面详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="pageId", alias="页面id", type="int", required="", min="1", description="页面id")
     * @ApiSuccess({"code":200,"result":{"pageId":1,"pageName":"index.html","pageTemplateId":1,"code":"<script>console.log('index')</script>","description":"默认页面请勿删除。","statisticEnabled":0,"statisticConfig":"","ipCost":"0.00","latestTime":"2023-10-27 14:43:54","status":1},"systemTimestamp":1699859260,"systemDateTime":"2023-11-13 15:07:40","msg":"OK"})
     */
    public function pageDetail()
    {
        $param = $this->request()->getRequestParam();

        try {

            $data = PageModel::create()->get($param['pageId']);
            $data = $data->hidden(['createTime', 'updateTime'])->toRawArray();

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 页面添加
     * @Api(name="页面添加",path="/Api/Admin/Navigation/Page/add")
     * @ApiDescription("页面添加")
     * @Method(allow=["POST"])
     * @Param(name="pageName", alias="页面名字", type="string", required="", mbLengthMin="1", description="页面名字")
     * @Param(name="pageTemplateId", alias="所属页面模板", type="int", required="", min="1", description="所属页面模板")
     * @Param(name="code", alias="附加代码", type="string", required="", description="附加代码")
     * @Param(name="description", alias="描述", type="string", required="", description="描述")
     * @Param(name="statisticEnabled", alias="统计扣量控制开关", type="int", required="", inArray=[1, 0], description="统计扣量控制开关")
     * @Param(name="statisticConfig", alias="统计扣量控制配置", type="string", required="", description="统计扣量控制配置")
     * @Param(name="ipCost", alias="单ip价格", type="float", required="", min="0", description="单ip价格")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'pageName' => trim($param['pageName']),
                'pageTemplateId' => intval($param['pageTemplateId']),
                'code' => trim($param['code']),
                'navCode' => trim($param['navCode']),
                'description' => trim($param['description']),
                'statisticEnabled' => intval($param['statisticEnabled']),
                'statisticConfig' => trim($param['statisticConfig']),
                'ipCost' => floatval($param['ipCost']),
                'status' => intval($param['status']),
            ];

            $result = PageService::getInstance()->addPage($data);

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
     * 页面修改
     * @Api(name="页面修改",path="/Api/Admin/Navigation/Page/edit")
     * @ApiDescription("页面修改")
     * @Method(allow=["POST"])
     * @Param(name="pageId", alias="页面id", type="int", required="", min="1", description="页面id")
     * @Param(name="pageName", alias="页面名字", type="string", required="", mbLengthMin="1", description="页面名字")
     * @Param(name="pageTemplateId", alias="所属页面模板", type="int", required="", min="1", description="所属页面模板")
     * @Param(name="code", alias="附加代码", type="string", optional="", description="附加代码")
     * @Param(name="description", alias="描述", type="string", optional="", description="描述")
     * @Param(name="statisticEnabled", alias="统计扣量控制开关", type="int", optional="", inArray=[1, 0], description="统计扣量控制开关")
     * @Param(name="statisticConfig", alias="统计扣量控制配置", type="string", optional="", description="统计扣量控制配置")
     * @Param(name="ipCost", alias="单ip价格", type="float", optional="", min="0", description="单ip价格")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1685962868,"systemDateTime":"2023-06-05 19:01:08","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'pageId' => intval($param['pageId']),
                'pageName' => trim($param['pageName']),
                'pageTemplateId' => intval($param['pageTemplateId']),
                'code' => trim($param['code']),
                'navCode' => trim($param['navCode']),
                'description' => trim($param['description']),
                'statisticEnabled' => intval($param['statisticEnabled']),
                'statisticConfig' => trim($param['statisticConfig']),
                'ipCost' => floatval($param['ipCost']),
                'status' => intval($param['status']),
            ];

            $result = PageService::getInstance()->editPage($data);

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
     * 页面修改状态
     * @Api(name="页面修改状态",path="/Api/Admin/Navigation/Page/setStatus")
     * @ApiDescription("页面修改状态")
     * @Method(allow=["POST"])
     * @Param(name="pageId", alias="页面id", type="int", required="", min="1", description="页面id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'pageId' => $param['pageId'],
                'status' => intval($param['status']),
            ];

            $result = PageService::getInstance()->editPage($data);

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
     * 页面删除
     * @Api(name="页面删除",path="/Api/Admin/Navigation/Page/delete")
     * @ApiDescription("页面删除")
     * @Method(allow=["POST"])
     * @Param(name="pageId", alias="页面id", type="int", required="", min="1", description="页面id")
     * @apiSuccess({"code":200,"result":1,"systemTimestamp":1686386747,"systemDateTime":"2023-06-10 16:45:47","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $page = PageModel::create()->get($param['pageId']);

            if (!$page) {
                throw new Exception('无效的页面id', Status::CODE_BAD_REQUEST);
            }

            if ($page->pageName == 'index.html') {
                throw new Exception('默认页面请勿删除', Status::CODE_BAD_REQUEST);
            }

            $result = $page->destroy();

            if ($result) {
                $filePath = SystemConfigKey::FRONTEND_PATH . '/' . $page->pageName;
                if (is_file($filePath)) {
                    unlink($filePath);
                }

                PageService::getInstance()->deletePageCache($page->pageName);
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

    /**
     * 页面生成
     * @Api(name="页面生成",path="/Api/Admin/Navigation/Page/create")
     * @ApiDescription("页面生成")
     * @Method(allow=["POST"])
     * @Param(name="pageId", alias="页面id", type="int", optional="", min="1", description="页面id")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1686910464,"systemDateTime":"2023-06-16 18:14:24","msg":"OK"})
     */
    public function create()
    {
        $param = $this->request()->getRequestParam();

        try {

            if (isset($param['pageId'])) {
                $page = PageModel::create()->get($param['pageId']);

                if (!$page) {
                    throw new Exception('无效的页面id', Status::CODE_BAD_REQUEST);
                }

                /*if ($page->pageName == 'index.html') {
                    throw new Exception('默认页面不用生成', Status::CODE_BAD_REQUEST);
                }*/

                $pageList[] = $page;

                // 无论生成单个页面还是全部页面都要先删除页面对应的模板数据缓存
                $pageTemplateIdList = [$page['pageTemplateId']];
            } else {
                $pageList = PageModel::create()
                    ->where([
                        // 'pageName' => ['index.html', '!='],
                        'status' => PageModel::STATE_NORMAL,
                    ])
                    ->all();

                if (!$pageList) {
                    throw new Exception('无有效的页面', Status::CODE_BAD_REQUEST);
                }

                // 无论生成单个页面还是全部页面都要先删除页面对应的模板数据缓存
                $pageTemplateIdList = array_column($pageList, 'pageTemplateId');
            }

            PageService::getInstance()->deleteTemplateCache($pageTemplateIdList);

            foreach ($pageList as $page) {
                $result = PageService::getInstance()->createStaticDataPage($page);
            }

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 页面扣量开关状态设置
     * @Api(name="页面扣量开关状态设置",path="/Api/Admin/Navigation/Page/setStatisticEnabled")
     * @ApiDescription("页面扣量开关状态设置")
     * @Method(allow=["POST"])
     * @Param(name="pageId", alias="页面id", type="int", required="", min="1", description="页面id")
     * @Param(name="statisticEnabled", alias="统计扣量控制开关", type="int", required="", inArray=[1, 0], description="统计扣量控制开关")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1692699050,"systemDateTime":"2023-08-22 18:10:50","msg":"OK"})
     */
    public function setStatisticEnabled()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'pageId' => $param['pageId'],
                'statisticEnabled' => intval($param['statisticEnabled']),
            ];

            $result = PageService::getInstance()->editPage($data);

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