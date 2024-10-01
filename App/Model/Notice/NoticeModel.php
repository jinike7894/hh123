
<?php
namespace App\Model\Notice;
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
class NoticeModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'notice';

    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'int';
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    const DELETED = 1;
    const NODELETE = 0;
}