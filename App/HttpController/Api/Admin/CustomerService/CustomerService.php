<?php
namespace App\HttpController\Api\Admin\CustomerService;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\CustomerService\CustomerServiceModel;
use EasySwoole\Http\Message\Status;
use Exception;
use Throwable;

class CustomerService extends AdminBase
{
   
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $field = [
                'id',
                'name',
                'use_type',
                'url',
                'is_del',
                'create_at',
                'update_at',
            ];
            $model = CustomerServiceModel::create();
            $result= $model
                ->where(["is_del"=>CustomerServiceModel::NO_DELETE])
                ->order("create_at","desc")
                ->all();
            foreach($result["list"] as $k=>$v){
                    switch($v["use_type"]){
                        case 1:
                            $result["list"][$k]["use_type"]="广告合作";
                            break;
                       case 2:
                            $result["list"][$k]["use_type"]="商务合作";
                            break;
                        case 3:
                            $result["list"][$k]["use_type"]="客服";
                            break;
                    }
            }
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function add()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'name'=>trim($param['name']),
                'use_type'=>trim($param['use_type']),
                'url'=>trim($param['url']),
                'create_at'=>time(),
                'update_at'=>time(),
            ];

            $result = CustomerServiceModel::create($data)->save();

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
    public function info()
    {
        $param = $this->request()->getRequestParam();
        try {
            $res = CustomerServiceModel::create()
                ->get([
                    'id' => $param['id'],
                ]);

        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    }
    public function edit()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'name' => trim($param['name']),
                'use_type' => trim($param['use_type']),
                'url' => trim($param['url']),
                'update_at' =>time(),
            ];

            $result = CustomerServiceModel::create()->update($data,["id"=>$param["id"]]);

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
    public function setStatus()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'is_del' => CustomerServiceModel::DELETE,
                "update_at"=>time(),
            ];

            $result = CustomerServiceModel::create()->update($data,["id"=>$param["id"]]);

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