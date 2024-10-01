<?php
namespace App\HttpController\Api\CustomerService;
use App\Enum\ConfigKey\SystemConfigKey;
use App\Enum\ConfigKey\AppConfigKey;
use App\HttpController\Api\ApiBase;
use App\HttpController\Api\User\UserBase;
use App\Model\User\UserModel;
use App\Model\CustomerService\CustomerServiceModel;
use App\Model\Post\PostReplyModel;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use App\Service\Oss\LocalOssService;
use Exception;
use Throwable;


class CustomerService extends UserBase
{
    //客服列表
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
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }
        return $this->writeJson(Status::CODE_OK, $result, Status::getReasonPhrase(Status::CODE_OK));
    }
  
   
}