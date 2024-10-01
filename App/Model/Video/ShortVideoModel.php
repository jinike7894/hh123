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
class ShortVideoModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_short_vod';

    protected $primaryKey = 'vodId';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['vodId']) && $keyword['vodId'] && $where['vodId'] = $keyword['vodId'];

        // 影视数据其实会采集很多，不建议在左边加百分号。
        // isset($keyword['vodName']) && $keyword['vodName'] && $where['vod_name'] = [$keyword['vodName'] . '%', 'LIKE'];
        isset($keyword['vodName']) && $keyword['vodName'] && $where['vodName'] = ['%' . $keyword['vodName'] . '%', 'LIKE'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }

}