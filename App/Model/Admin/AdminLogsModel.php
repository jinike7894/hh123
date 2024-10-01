<?php

namespace App\Model\Admin;

use EasySwoole\ORM\AbstractModel;

/**
 * Class AdminLogsModel
 * @package App\Model\Admin
 * @property $id int | id
 * @property $adminId int | id
 * @property $type enum | 操作类型(Add 添加，Update 修改，Delete 删除)
 * @property $logModule string | 操作模块
 * @property $action string | 方法名称
 * @property $authId int | 关联权限表id
 * @property $status int | (0失败，1成功)
 * @property $content string | 操作内容
 * @property $requestIp int | 操作ip
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class AdminLogsModel extends AbstractModel
{
    protected $tableName = 'admin_logs';

    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const TYPE_SELECT = 'Select';
    const TYPE_ADD = 'Add';
    const TYPE_UPDATE = 'Update';
    const TYPE_DELETE = 'Delete';

    /**
     * 记录用户日志
     * @param int $userId
     * @param string $action
     * @param int $requestIp
     * @param string $type
     * @param int $status
     * @param string $content
     * @return bool|int
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function addLog(int $userId, string $action, int $requestIp, string $type, int $status, $content)
    {
        //根据访问地址获取模块名称
        $auths = AuthModel::create()->get(['authRule' => $action, 'isLog' => 1]);
        if (!$auths) {
            return false;
        }
        $this->action = $action ?? '';
        $this->adminId = $userId ?? 0;
        $this->type = $type ?? self::TYPE_ADD;
        $this->authId = $auths->authId ?? 0;
        $this->logModule = $auths->authName ?? '未定义模块';
        $this->requestIp = $requestIp ?? 0;
        $this->status = $status ?? 1;
        $this->content = isset($content) ? $content : '';
        return $this->save();
    }

    /**
     * @getAll
     * @param int $page 1
     * @param int $pageSize 10
     * @param string $field *
     * @return array[total,list]
     */
    public function getAll(int $page = 1, array $keyword = [], int $pageSize = 20, string $field = '*'): array
    {
        $where = [];
        if (!empty($keyword)) {
            !empty($keyword['adminId']) && $where['a.adminId'] = $keyword['adminId'];
            if (!empty($keyword['adminAccount'])) {
                $adminInfo = AdminModel::create()->get(['adminAccount' => $keyword['adminAccount']]);
                if (!$adminInfo) {
                    return ['total' => 0, 'list' => []];
                } else {
                    $where['a.adminId'] = $adminInfo->adminId;
                }
            }
            !empty($keyword['logModuleId']) && $where['a.authId'] = $keyword['logModuleId'];
            $where['a.createTime'] = [[$keyword['startTime'], $keyword['endTime']], 'between'];
        }
        $field = 'a.createTime,ad.adminAccount,a.adminId,a.requestIp,au.authName as logModule,a.content,a.status,a.authId';
        $list = $this->connection('read', true)
            ->withTotalCount()
            ->alias('a')
            ->order($this->schemaInfo()->getPkFiledName(), 'DESC')
            ->field($field)
            ->join('auths au', 'au.authRule = a.action', 'LEFT')
            ->join('admin ad', 'a.adminId=ad.adminId', 'LEFT')
            ->limit($pageSize * ($page - 1), $pageSize)
            ->all($where);
        foreach ($list as $val) {
            $val->status = $val->status == 1 ? '成功' : '失败';
            $val->requestIp = $val->requestIp ? long2ip($val->requestIp) : '';
            if($val->authId == '-1'){
                $val->logModule = "登录";
            }
        }
        $total = $this->lastQueryResult()->getTotalCount();
        return ['total' => $total, 'list' => $list];
    }
}
