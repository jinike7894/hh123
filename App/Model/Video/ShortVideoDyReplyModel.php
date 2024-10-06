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
class ShortVideoDyReplyModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_short_vod_dy_reply';
    protected $primaryKey = 'id';
    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

   

}