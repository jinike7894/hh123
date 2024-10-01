<?php

namespace App\Service\Video;

use App\Enum\RedisDb;
use App\Model\Video\VideoModel;
use App\RedisKey\Video\VideoKey;
use EasySwoole\Component\Singleton;
use EasySwoole\RedisPool\RedisPool;

class VideoService
{
    use Singleton;

    /**
     * 获取首页数据
     * @param $pageSize
     * @return array
     * @throws \EasySwoole\ORM\Exception\Exception
     * @throws \EasySwoole\Redis\Exception\RedisException
     * @throws \Throwable
     */
    public function getIndexData($pageSize)
    {
        $redis = RedisPool::defer();
        $key = VideoKey::VideoIndexData();
        $data = $redis->get($key);

        if (!$data) {

            // 首页banner vod_level=9
            // 首页顶部推荐 vod_level=1
            // 首页最新 按照id倒序
            // 启用的几个分类的推荐数据

            // 拿到所有的分类筛选项
            $typeList = TypeService::getInstance()->getVideoFilterTypeList();

            $videoList = [
                'bannerList' => [],
                'topList' => [],
                'latestList' => [],
                'typeList' => [],
            ];

            // 拿分类对应的列表
            foreach ($typeList as $typeItem) {
                $typeRecommendedList = VideoModel::create()->limit($pageSize)->getRecommendedListByType($typeItem['typeId']);

                $videoList['typeList'][] = [
                    'typeName' => $typeItem['typeName'],
                    'videoList' => $typeRecommendedList,
                ];
            }

            // 准备限制视频范围
            $keyword = [];
            $typeId1 = TypeService::getInstance()->getVideoPidList();
            $typeId1 && $keyword['typeId1'] = $typeId1;

            $videoList['bannerList'] = VideoModel::create()->getVideoLevelList(VideoModel::LEVEL_BANNER, $keyword);
            $videoList['topList'] = VideoModel::create()->limit($pageSize)->getVideoLevelList(VideoModel::LEVEL_TOP, $keyword);
            $videoList['latestList'] = VideoModel::create()->limit($pageSize)->getLatestList($keyword);

            $data = [
                'typeList' => $typeList,
                'videoList' => $videoList,
            ];

            $redis->set($key, $data, 60);
        }

        return $data;
    }

    /**
     * 记录搜索热词
     * @param $word
     */
    public function setHotWords($word)
    {

        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $redis->zInCrBy(VideoKey::hotWord(), 1, $word);
        $redis->expire(VideoKey::hotWord(), 86400);
    }

    /**
     * 获取搜索热词
     * @return array
     */
    public function getHotWords()
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $words = $redis->zRevRange(VideoKey::hotWord(), 0, 20, true);
        return array_keys($words);
    }

    /**
     * 记录搜索热词
     * @param $word
     */
    public function setAdultHotWords($word)
    {

        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $redis->zInCrBy(VideoKey::adultHotWord(), 1, $word);
        $redis->expire(VideoKey::adultHotWord(), 86400);
    }

    /**
     * 获取搜索热词
     * @return array
     */
    public function getAdultHotWords()
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $words = $redis->zRevRange(VideoKey::adultHotWord(), 0, 20, true);
        return array_keys($words);
    }

    /**
     * 转换视频源为中文
     * @param $source
     * @return string
     */
    public function convertSourceText($source)
    {
        $search = [
            'hym3u8',
            'subm3u8',
            'slm3u8',
            'qhm3u8',
            'hhm3u8',
            'clm3u8',
            'bmm3u8',
            'bfzym3u8',
            '155m3u8',
            'gsm3u8',
            'jsm3u8',
            'kkm3u8',
            'smzy',
            'zuidam3u8',
            'hnm3u8',
            'wjm3u8',
            'sdm3u8',
            'xlm3u8',
            'kcm3u8',
            'jinyingm3u8',
            'lzm3u8',
            '1080zyk',
            'kuaikan',
            'mdm3u8',
            'lyh',
            'skm3u8',
            'okm3u8',
        ];
        $replace = [
            '虎牙资源',
            '速播资源',
            '森林资源',
            '奇虎资源',
            '火狐资源',
            '草榴资源',
            '博民资源',
            '暴风资源',
            '155资源',
            '光速云资源',
            '极速在线',
            'KK在线',
            '神马云播',
            '最大在线',
            '红牛在线',
            '无尽',
            '闪电播放',
            '新浪资源',
            '快车资源',
            '金鹰资源',
            '量子资源',
            '优质资源',
            '快看资源',
            '真不卡',
            '狼友会',
            '速看',
            'OK资源',
        ];
        return str_replace($search, $replace, $source);
    }
}