<?php

namespace App\Enum;

class AccountStatus
{
    const STATE_DELETED = -1; // 删除状态
    const STATE_FORBIDDEN = 0; // 禁用状态
    const STATE_NORMAL = 1; // 正常状态

    const STATUS_TEXT = [
        AccountStatus::STATE_NORMAL => '正常',
        AccountStatus::STATE_FORBIDDEN => '禁用',
        AccountStatus::STATE_DELETED => '已删除',
    ];
}