<?php

namespace App\RedisKey\Video;

class TypeKey
{
    /**
     * Av分类列表
     * @return string
     */
    public static function AdultTypeList($module)
    {
        return 'Type_AdultTypeList_' . $module;
    }

    /**
     * 影视筛选分类列表
     * @return string
     */
    public static function VideoFilterTypeList()
    {
        return 'Type_VideoFilterTypeList';
    }

}