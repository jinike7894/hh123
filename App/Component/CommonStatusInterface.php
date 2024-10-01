<?php

namespace App\Component;

interface CommonStatusInterface
{
    const STATE_NORMAL = 1;
    const STATE_FORBIDDEN = 0;
    const STATE_DELETED = -1;

    const STATUS_TEXT = [
        self::STATE_NORMAL => '正常',
        self::STATE_FORBIDDEN => '禁用',
        self::STATE_DELETED => '已删除',
    ];
}