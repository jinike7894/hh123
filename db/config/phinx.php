<?php

return
    [
        'paths' => [
            'migrations' => [
                '%%PHINX_CONFIG_DIR%%/../migrations/initDb', // 基础模块
                '%%PHINX_CONFIG_DIR%%/../migrations/navigation', // 导航模块
                '%%PHINX_CONFIG_DIR%%/../migrations/channel',  // 渠道安装模块
                '%%PHINX_CONFIG_DIR%%/../migrations/article',  // 文章模块
                '%%PHINX_CONFIG_DIR%%/../migrations/video',  // 视频模块
                '%%PHINX_CONFIG_DIR%%/../migrations/user',  // 用户模块
                '%%PHINX_CONFIG_DIR%%/../migrations/prostitute',  // 楼凤模块
                '%%PHINX_CONFIG_DIR%%/../migrations/common',  // 公共模块
                '%%PHINX_CONFIG_DIR%%/../migrations/payment',  // 支付模块
            ],
            'seeds' => '%%PHINX_CONFIG_DIR%%/../seeds'
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'production',
            'production' => [
                'adapter' => 'mysql',
                'host' => 'localhost', // ← 运维大哥改这里
                'name' => 'production_db', // ← 运维大哥改这里
                'user' => 'root', // ← 运维大哥改这里
                'pass' => '', // ← 运维大哥改这里
                'port' => '3306', // ← 运维大哥改这里
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ],
        ],
        'version_order' => 'creation'
    ];
