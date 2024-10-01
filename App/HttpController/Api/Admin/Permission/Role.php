<?php

namespace App\HttpController\Api\Admin\Permission;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Admin\RoleModel;
use App\Service\Permission\RoleService;
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
 * Class Role
 * @package App\HttpController\Api\Admin\Permission
 * @ApiGroup(groupName="后台-系统-角色 Admin/Permission/Role")
 * @ApiGroupDescription("后台系统角色相关。")
 * @ApiGroupAuth(name="authorization", from={HEADER}, type="string", required="", description="用户登录后，服务端返回的JWT，用于API鉴权")
 */
class Role extends AdminBase
{
    /**
     * 角色列表
     * @Api(name="角色列表",path="/Api/Admin/Permission/Role/roleList")
     * @ApiDescription("角色列表")
     * @Method(allow=["GET", "POST"])
     * @Param(name="page", alias="页码", type="int", defaultValue="1", min="1", integer="", description="当前页数")
     * @Param(name="pageSize", alias="每页显示条数", type="int", defaultValue="20", min="1", max="100", integer="", description="每页显示条数")
     * @Param(name="roleId", alias="角色id", type="int", optional="", min="1", description="角色id")
     * @Param(name="status", alias="状态", type="int", optional="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":{"total":3,"list":[{"roleId":1,"roleName":"超级管理员","remark":"这个未区分权限，默认不要用，只留一个就好。","status":1},{"roleId":2,"roleName":"总后台管理员","remark":"选角色的时候选这个不要选超级管理员。","status":1},{"roleId":3,"roleName":"商户总管理员","remark":"添加商户的时候默认这个角色。","status":1}],"options":[]},"systemTimestamp":1704120165,"systemDateTime":"2024-01-01 22:42:45","msg":"OK"})
     */
    public function roleList()
    {
        $param = $this->request()->getRequestParam();

        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);

            isset($param['roleId']) && $keyword['roleId'] = intval($param['roleId']);
            isset($param['status']) && $keyword['status'] = intval($param['status']);

            $field = [
                'roleId',
                'roleName',
                'remark',
                'status',
            ];

            $data = RoleModel::create()
                ->getAll($page, $keyword, $pageSize, $field);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 角色详情
     * @Api(name="角色详情",path="/Api/Admin/Permission/Role/roleDetail")
     * @ApiDescription("角色详情")
     * @Method(allow=["GET", "POST"])
     * @Param(name="roleId", alias="角色id", type="int", required="", min="1", description="角色id")
     * @ApiSuccess({"code":200,"result":{"roleId":1,"roleName":"超级管理员","status":1,"remark":"这个未区分权限，默认不要用，只留一个就好。","createTime":"2022-03-22 16:20:47","updateTime":"2022-05-17 14:03:55"},"systemTimestamp":1704120323,"systemDateTime":"2024-01-01 22:45:23","msg":"OK"})
     */
    public function roleDetail()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = RoleModel::create()->get($param['roleId']);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    /**
     * 角色添加
     * @Api(name="角色添加",path="/Api/Admin/Permission/Role/add")
     * @ApiDescription("角色添加")
     * @Method(allow=["POST"])
     * @Param(name="roleName", alias="角色名", type="string", required="", mbLengthMin="1", mbLengthMax="40", description="角色名")
     * @Param(name="remark", alias="备注", type="string", required="", mbLengthMin="0", mbLengthMax="100", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":4,"systemTimestamp":1704123536,"systemDateTime":"2024-01-01 23:38:56","msg":"OK"})
     */
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'roleName' => trim($param['roleName']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
            ];

            $result = RoleService::getInstance()->addRole($data);

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
     * 角色编辑
     * @Api(name="角色编辑",path="/Api/Admin/Permission/Role/edit")
     * @ApiDescription("角色编辑")
     * @Method(allow=["POST"])
     * @Param(name="roleId", alias="角色id", type="int", required="", min="1", description="角色id")
     * @Param(name="roleName", alias="角色名", type="string", required="", mbLengthMin="1", mbLengthMax="40", description="角色名")
     * @Param(name="remark", alias="备注", type="string", required="", mbLengthMin="0", mbLengthMax="100", description="备注")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1704123590,"systemDateTime":"2024-01-01 23:39:50","msg":"OK"})
     */
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'roleId' => intval($param['roleId']),
                'roleName' => trim($param['roleName']),
                'remark' => trim($param['remark']),
                'status' => intval($param['status']),
            ];

            $result = RoleService::getInstance()->editRole($data);

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
     * 角色修改状态
     * @Api(name="角色修改状态",path="/Api/Admin/Permission/Role/setStatus")
     * @ApiDescription("角色修改状态")
     * @Method(allow=["POST"])
     * @Param(name="roleId", alias="角色id", type="int", required="", min="1", description="角色id")
     * @Param(name="status", alias="状态", type="int", required="", inArray=[1, 0], description="状态")
     * @apiSuccess({"code":200,"result":true,"systemTimestamp":1704123644,"systemDateTime":"2024-01-01 23:40:44","msg":"OK"})
     */
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'roleId' => $param['roleId'],
                'status' => intval($param['status']),
            ];

            $result = RoleService::getInstance()->editRole($data);

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
     * 角色删除
     * @Api(name="角色删除",path="/Api/Admin/Permission/Role/delete")
     * @ApiDescription("角色删除")
     * @Method(allow=["POST"])
     * @Param(name="roleId", alias="角色id", type="int", required="", min="1", description="角色id")
     * @ApiSuccess({"code":200,"result":true,"systemTimestamp":1704124191,"systemDateTime":"2024-01-01 23:49:51","msg":"OK"})
     */
    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'roleId' => $param['roleId'],
                'status' => RoleModel::STATE_DELETED,
            ];

            $result = RoleService::getInstance()->editRole($data);

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
}