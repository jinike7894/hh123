<?php
namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

/**
 * Class ShortVideoModel
 * @package App\Model\Video
 * @property $vodId int | 视频id
 * @property $vodName string | 视频名
 * @property $vodPic string | 视频图片
 * @property $vodPlayUrl string | 视频播放地址
 * @property $fileType string | 文件类型
 * @property $likeCount int | 假的用来显示的点赞数
 * @property $sort int | 排序
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class ShortVideoTagModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_short_video_tag';

    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'int';
    protected $createTime = 'create_at';
    protected $updateTime = 'update_at';
    const DELETED = 1;
    const NODELETE = 0;

}