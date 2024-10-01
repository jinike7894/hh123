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
            'default_environment' => 'development',
            'development' => [
                'adapter' => 'mysql',
                'host' => '172.24.37.4',
                'name' => 'matcha',
                'user' => 'root',
                'pass' => 'qwe123',
                'port' => '3307',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ],
            'testing' => [
                'adapter' => 'mysql',
                'host' => '172.26.96.1',
                'name' => 'esdhPhinx',
                'user' => 'root',
                'pass' => 'qwe123',
                'port' => '3307',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_general_ci',
            ]
        ],
        'version_order' => 'creation'
    ];
