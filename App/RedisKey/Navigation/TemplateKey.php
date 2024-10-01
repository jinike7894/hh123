<?php

namespace App\RedisKey\Navigation;

class TemplateKey
{
    /**
     * 页面模板关联数据缓存
     * @param $pageTemplateId
     * @return string
     */
    public static function data($pageTemplateId)
    {
        return 'TemplateData_' . $pageTemplateId;
    }

    /**
     * 页面模板缓存
     * @param $pageTemplateId
     * @return string
     */
    public static function cache($pageTemplateId)
    {
        return 'TemplateCache_' . $pageTemplateId;
    }

    /**
     * config缓存
     * @param $pageId
     * @return string
     */
    public static function pageConfigCache($pageId)
    {
        return 'PageCache_' . $pageId;
    }

    /**
     * 楼风缓存
     * @param $page
     * @return string
     */
    public static function prostitutePageCache($page)
    {
        return 'prostitutePageCache' . $page;
    }

    /**
     * 楼风类型缓存
     * @param $module
     * @return string
     */
    public static function prostituteTypePageCache($module)
    {
        return 'prostituteTypePageCache' . $module;
    }

    /**
     * 视频缓存
     * @param $page
     * @return string
     */
    public static function adultPageCache($page)
    {
        return 'adultPageCache' . $page;
    }
}