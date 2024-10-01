
<?php

namespace App\HttpController\Api\Admin\Video;
use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use EasySwoole\Http\Message\Status;
use App\Model\Video\ShortVideoTagModel;
use App\Model\Admin\AdminLogsModel;
use Throwable;


class ShortVideoTag extends AdminBase
{
    //后台短视频tag列表
    public function list(){
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'name',
                'img_src',
                'sort',
                'create_at',
            ];
            $data = ShortVideoTagModel::create()
                ->where(["is_del"=>ShortVideoTagModel::NODELETE])
                ->order("sort","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //猎奇短视频tag-单个查看
    public function info(){
        $param = $this->request()->getRequestParam();
        try {
            $field = [
               'id',
                'name',
                'img_src',
                'sort',
                'create_at',
            ];
            $data = ShortVideoTagModel::create()
                ->get(["is_del"=>ShortVideoTagModel::NODELETE,"id"=>$param["id"]]);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }
    //猎奇短视频tag编辑
    public function edit(){
        $param = $this->request()->getRequestParam();
        try {
            $data = [
                'name' => intval($param['name']),
                'img_src' => trim($param['img_src']),
                'sort' => trim($param['sort']),
                'update_at' => time(),
            ];

            $result = ShortVideoTagModel::create()->update($data,["id"=>$param["id"]]);

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
    //猎奇短视频tag-删除
    public function delete(){
        $param = $this->request()->getRequestParam();
        try {
            $data = [
                'is_del' =>ShortVideoTagModel::DELETED,
                'update_at' => time(),
            ];

            $result = ShortVideoTagModel::create()->update($data,["id"=>$param["id"]]);

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