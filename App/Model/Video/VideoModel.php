<?php

namespace App\Model\Video;

use App\Component\CommonStatusInterface;
use App\Model\BaseModel;

class VideoModel extends BaseModel implements CommonStatusInterface
{
    protected $tableName = 'mac_vod';

    protected $primaryKey = 'vod_id';

    // 这个级别跟maccms的定义一样
    const LEVEL_TOP = 1;
    const LEVEL_2 = 2;
    const LEVEL_3 = 3;
    const LEVEL_4 = 4;
    const LEVEL_5 = 5;
    const LEVEL_6 = 6;
    const LEVEL_7 = 7;
    const LEVEL_8 = 8;
    const LEVEL_BANNER = 9; // 这个是banner
    
    const FILE_TYPE_UP = 'up';
    const FILE_TYPE_URL = 'url';
    const FILE_TYPE_AWS_S3 = 'awsS3';
    public function parseKeywordToWhere($keyword)
    {
        $where = [];

        if (isset($keyword['typeId']) && $keyword['typeId']) {
            if (is_array($keyword['typeId'])) {
                $where['type_id'] = [$keyword['typeId'], 'IN'];
            } else {
                $where['type_id'] = $keyword['typeId'];
            }
        }

        // 这个type_id_1 就是顶级id的意思
        if (isset($keyword['typeId1'])) {
            if (is_numeric($keyword['typeId1'])) {
                if (isset($where['type_id'])) {
                    // 如果设置了type_id就正常处理
                    $where['type_id_1'] = $keyword['typeId1'];
                } else {
                    // 这里有个特殊的情况，就是只有父级分类没有type_id的情况下，需要设置一个or的参数
                    $this->where('(type_id = ? OR type_id_1 = ?)', [$keyword['typeId1'], $keyword['typeId1']]);
                }
            }
            if (is_array($keyword['typeId1'])) {
                if (count($keyword['typeId1']) > 1) {
                    $where['type_id_1'] = [$keyword['typeId1'], 'IN'];
                } elseif (count($keyword['typeId1']) == 1) {
                    $where['type_id_1'] = current($keyword['typeId1']);
                }
            }
        }

        isset($keyword['vodArea']) && $keyword['vodArea'] && $where['vod_area'] = $keyword['vodArea'];
        isset($keyword['vodLang']) && $keyword['vodLang'] && $where['vod_lang'] = $keyword['vodLang'];
        isset($keyword['vodYear']) && $keyword['vodYear'] && $where['vod_year'] = $keyword['vodYear'];

        // 影视数据其实会采集很多，不建议在左边加百分号。
        // isset($keyword['vodName']) && $keyword['vodName'] && $where['vod_name'] = [$keyword['vodName'] . '%', 'LIKE'];
        isset($keyword['vodName']) && $keyword['vodName'] && $where['vod_name'] = ['%' . $keyword['vodName'] . '%', 'LIKE'];

        isset($keyword['vodTime']) && $where['vod_time'] = $keyword['vodTime'];
        isset($keyword['vodTimeAdd']) && $where['vod_time_add'] = $keyword['vodTimeAdd'];

        if (isset($keyword['status']) && strlen($keyword['status']) > 0) {
            $where['vod_status'] = $keyword['status'];
        } else {
            // 这个是一开始写反了，所以也没必要改，就这样就行。
            // 默认参数是查询正常的
            $where['vod_status'] = self::STATE_NORMAL;
        }

        return $where;
    }

    /**
     * 按照视频等级获取列表
     * @param $level
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getVideoLevelList($level, $keyword = [])
    {
        if ($keyword) {
            $this->where($this->parseKeywordToWhere($keyword));
        }

        if ($level == VideoModel::LEVEL_BANNER) {
            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'IF(CHAR_LENGTH(vod_pic_slide) > 0, vod_pic_slide, vod_pic) AS vodPic', // 如果是banner用的是vod_pic_slide
                'vod_remarks AS vodRemarks',
            ];
        } else {
            $field = [
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic_thumb AS vodPic',
                'vod_remarks AS vodRemarks',
            ];
        }

        return $this
            ->field($field)
            ->where(['vod_level' => $level, 'vod_status' => VideoModel::STATE_NORMAL])
            ->order('vod_id', 'DESC')
            ->all();
    }

    /**
     * 按照视频类型获取推荐列表
     * @param $typeId
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getRecommendedListByType($typeId)
    {
        return $this
            ->field([
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic_thumb AS vodPic',
                'vod_pic2 AS vodPic2',
                'vod_remarks AS vodRemarks',
            ])
            ->where('(type_id = ? OR type_id_1 = ?)', [$typeId, $typeId])
            ->where('vod_status', VideoModel::STATE_NORMAL)
            ->order('vod_level', 'DESC')
            ->order('vod_id', 'DESC')
            ->all();
    }

    /**
     * 获取最新列表
     * @param array $keyword
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getLatestList($keyword = [])
    {
        if ($keyword) {
            $this->where($this->parseKeywordToWhere($keyword));
        }

        return $this
            ->field([
                'vod_id AS vodId',
                'vod_name AS vodName',
                'vod_pic_thumb AS vodPic',
                'vod_remarks AS vodRemarks',
            ])
            ->where(['vod_status' => VideoModel::STATE_NORMAL])
            ->order('vod_id', 'DESC')
            ->all();
    }

    /**
     * 根据图片左边开头字母匹配
     * @param $picLeft
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getListByNotEqualToPicLeft($picLeft)
    {
        $length = mb_strlen($picLeft);
        return $this
            ->where(['vod_status' => VideoModel::STATE_NORMAL])
            ->where("left(vod_pic, {$length}) != '{$picLeft}'")
            ->where("left(vod_pic, 4) = 'http'") // 这个是排除了所有本地图片，如果情况有变就再说。
            ->all();
    }

    /**
     * 根据图片右边.xyz匹配
     * @return array|bool|\EasySwoole\ORM\Collection\Collection|\EasySwoole\ORM\Db\Cursor|\EasySwoole\ORM\Db\CursorInterface
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \Throwable
     */
    public function getListBySuffix()
    {
        return $this
            ->where(['vod_status' => VideoModel::STATE_NORMAL])
            ->where("right(vod_pic, 4) != '.xyz'")
            ->all();
    }
}