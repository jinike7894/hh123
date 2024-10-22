<?php

namespace App\Model\Live;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;
use App\Model\Prostitute\ProstituteModel;

/**
 * Class LiveModel
 * @package App\Model\Live
 * @property $liveId int | 直播id
 * @property $liveTitle string | 直播标题
 * @property $fileType string | 文件类型
 * @property $liveCover string | 直播封面
 * @property $liveViewers int | 直播人数
 * @property $liveUrl string | 直播链接
 * @property $streamerNickname string | 主播昵称
 * @property $streamerAvatar string | 主播头像
 * @property $sort int | 排序
 * @property $status int | 状态
 * @property $createTime datetime | 创建时间
 * @property $updateTime datetime | 更新时间
 */
class LiveNewModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'live';
    protected $primaryKey = 'id';
    protected $autoTimeStamp = 'datetime';
    protected $createTime = 'createTime';
    protected $updateTime = 'updateTime';

    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';

    public function prostituteRelation()
    {
        return $this->hasOne(ProstituteModel::class, null, 'prostituteId', 'prostituteId');
    }

    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        isset($keyword['liveId']) && $keyword['liveId'] > 0 && $where['liveId'] = $keyword['liveId'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['status'] = $keyword['status'];
        } else {
            // 默认参数是不查询删除的
            $where['status'] = [self::STATE_DELETED, '>'];
        }

        return $where;
    }
}