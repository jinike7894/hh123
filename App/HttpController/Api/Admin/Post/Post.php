
<?php

namespace App\HttpController\Api\Admin\Post;

use App\Enum\ConfigKey\SystemConfigKey;
use App\HttpController\Api\Admin\AdminBase;
use App\Model\Admin\AdminLogsModel;
use App\Model\Live\LiveModel;
use App\Model\Post\PostModel;
use EasySwoole\Http\Message\Status;

use Exception;
use Throwable;

class Post extends AdminBase
{
   //帖子列表
    public function list()
    {
        $param = $this->request()->getRequestParam();
        try {
            $keyword = [];
            $page = (int)($param['page'] ?? 1);
            $pageSize = (int)($param['pageSize'] ?? SystemConfigKey::PAGE_SIZE);
            $field = [
                'id',
                'title',
                'type',
                "is_recommend",
                "vodio_src",
                "img_src",
                "create_at",
                'update_at',
            ];
            $data = PostModel::create()
                ->where(["is_del"=>PostModel::NO_DELETE])
                ->order("create_at","desc")
                ->getAll($page, $keyword, $pageSize, $field);
        } catch (Throwable $e) {
            return $this->writeJson($e->getCode(), [], $e->getMessage());
        }

        return $this->writeJson(Status::CODE_OK, $data, Status::getReasonPhrase(Status::CODE_OK));
    }

    // public function info()
    // {
    //     $param = $this->request()->getRequestParam();

    //     try {
    //         $res = PostModel::create()
    //             ->get([
    //                 'id' => $param['id'],
    //             ]);

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }
    //     return $this->writeJson(Status::CODE_OK, $res, Status::getReasonPhrase(Status::CODE_OK));
    // }

    // public function edit()
    // {
    //     $param = $this->request()->getRequestParam();
    //     if(isset($param["uid"])&&$param["uid"]!=""){
    //         $param["property"]=2;  //个人
    //     }else{
    //         $param["property"]=1;  //全员
    //     }
    //     try {
    //         $data = [
    //             'type' => intval($param['type']),
    //             'title' => trim($param['title']),
    //             'content' => trim($param['content']),
    //             'property' => trim($param['property']),
    //             'uid' => intval($param['uid']),
    //             'update_at' =>time(),
    //         ];

    //         $result = NoticeModel::create()->update($data,["id"=>$param["id"]]);

    //     } catch (Throwable $e) {
    //         return $this->writeJson($e->getCode(), [], $e->getMessage());
    //     }

    //     return $this->writeJson(
    //         Status::CODE_OK,
    //         $result,
    //         Status::getReasonPhrase(Status::CODE_OK),
    //         AdminLogsModel::TYPE_UPDATE,
    //         json_encode($param, JSON_UNESCAPED_UNICODE)
    //     );
    // }


    public function delete()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'is_del' => PostModel::DELETE,
                "update_at"=>time(),
            ];

            $result = PostModel::create()->update($data,["id"=>$param["id"]]);

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
    //设置推荐
    public function setRecommod()
    {
        $param = $this->request()->getRequestParam();

        try {
            $data = [
                'is_recommend' => 1,
                "update_at"=>time(),
            ];

            $result = PostModel::create()->update($data,["id"=>$param["id"]]);

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