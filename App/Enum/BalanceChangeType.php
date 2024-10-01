<?php

namespace App\Enum;

class BalanceChangeType
{
    /**
     * 账变类型
     * 1.调整加币 + ManualAdd
     * 2.调整减币 - ManualReduce
     * 3.点击计费 - Click
     */
    const TYPE_MANUAL_ADD = 'ManualAdd';
    const TYPE_MANUAL_REDUCE = 'ManualReduce';
    const TYPE_CLICK = 'Click';

    const TYPE_ALL = [
        self::TYPE_MANUAL_ADD,
        self::TYPE_MANUAL_REDUCE,
        self::TYPE_CLICK,
    ];

    const TYPE_ALL_TEXT = [
        self::TYPE_MANUAL_ADD => '调整加币',
        self::TYPE_MANUAL_REDUCE => '调整减币',
        self::TYPE_CLICK => '点击计费',
    ];

    const TYPE_ALL_LIST = [
        ['key' => self::TYPE_MANUAL_ADD, 'name' => '调整加币'],
        ['key' => self::TYPE_MANUAL_REDUCE, 'name' => '调整减币'],
        ['key' => self::TYPE_CLICK, 'name' => '点击计费'],
    ];
}