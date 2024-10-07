<?php
namespace App\Model\Post;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use EasySwoole\Mysqli\QueryBuilder;

/**
 * Class BannerModel
 * @package App\Model\Banne
 * @property $id int | id
 * @property $img_src string | 地址
 * @property $name string | 名称
 * @property $sort string | 排序
 * @property $status string | 状态
 * @property $create_at string | 添加时间
 */
class PostReplyModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_post_reply';

    protected $primaryKey = 'id';
    // const DELETE = 1;
    // const NO_DELETE = 0;
}