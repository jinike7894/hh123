<?php

namespace App\RedisKey\Navigation;

class PageKey
{
    /**
     * 页面统计数据
     * @param $date
     * @param $pageId
     * @return string
     */
    public static function statisticPv($date, $pageId)
    {
        return 'Page_Stat_Pv_' . $date . '_' . $pageId;
    }

    public static function statisticIp($date, $pageId)
    {
        return 'Page_Stat_Ip_' . $date . '_' . $pageId;
    }

    public static function landpageStatisticIp($date, $channelKey)
    {
        return 'LandPage_Stat_Ip_' . $date . '_' . $channelKey;
    }

    /**
     * 统计锁
     * @param $pageId
     * @return string
     */
    public static function statisticLock($pageId)
    {
        return 'PageStatLock_' . $pageId;
    }

    /**
     * 页面访问统计临时ip的bitmap缓存键
     * @return string
     */
    public static function statisticTempIpLongBit($date, $pageId, $step)
    {
        return 'PageStatTempIpLongBit_' . $date . '_' . $pageId . '_' . $step;
    }

    /**
     * 页面访问统计临时ip的hash缓存键
     * @return string
     */
    public static function statisticTempIpLongHash($date, $pageId)
    {
        return 'PageStatTempIpLongHash_' . $date . '_' . $pageId;
    }

    /**
     * 点击下载按钮临时ip的hash缓存键
     * @return string
     */
    public static function statisticDownloadClickTempIpLongHash($date, $pageId)
    {
        return 'DownloadBtnStatTempIpLongHash_' . $date . '_' . $pageId;
    }

    /**
     * 落地页点击统计临时ip的hash缓存键
     * @return string
     */
    public static function statisticClickTempIpLongHash($date, $pageId)
    {
        return 'PageClickStatTempIpLongHash_' . $date . '_' . $pageId;
    }

    /**
     * 页面缓存
     * @param $pageName
     * @return string
     */
    public static function cache($pageName)
    {
        return 'PageCache_' . $pageName;
    }
}