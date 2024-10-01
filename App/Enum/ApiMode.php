<?php

namespace App\Enum;

class ApiMode
{
    // 如果要加新的用位左移数定义 就是 1 2 4 8 16
    // 然后将 ALL 值改成最新的和值即可

    // 前台API
    const MODE_FRONT_API = 1;
    // 后台API
    const MODE_ADMIN_API = 2;
    // 所有
    const MODE_ALL = 3;
}