<?php

use EasySwoole\Log\LoggerInterface;
use App\Enum\ApiMode;

return [
    'API_MODE' => ApiMode::MODE_ALL,
    'ENABLE_PROCESS' => true, // 当前机器开启process
    'ENABLE_CRONTAB' => true, // 当前机器开启crontab
    'SERVER_NAME' => 'EasySwoole',
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9603,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3,
            // (可选参数）使用 http 上传大文件时可以进行配置
            'package_max_length' => 100 * 1024 * 1024, // 即 100 M
        ],
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 60
        ]
    ],
    'LOG' => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler' => null,
        'logConsole' => true,
        'displayConsole' => true,
        'ignoreCategory' => []
    ],
    'TEMP_DIR' => null,

    /*################ MYSQL CONFIG ##################*/
    'MYSQL' => [
        'host'          => '127.0.0.1', // 数据库地址
        'port'          => 3306, // 数据库端口
        'user'          => 'hh123', // 数据库用户名
        'password'      => 'hh123', // 数据库用户密码
        'database'      => 'hh123', // 数据库名
        'timeout'       => 45, // 数据库连接超时时间
        'charset'       => 'utf8', // 数据库字符编码
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => false, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 150, // 设置 连接池最大数量
        'minObjectNum'  => 25, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],

    /*################ REDIS CONFIG ##################*/
    'REDIS' => [
        'host'          => '127.0.0.1', // Redis 地址
        'port'          => '6379', // Redis 端口
        'auth'          => '', // Redis 密码
        'timeout'       => 3.0, // Redis 操作超时时间
        'reconnectTimes'=> 3, // Redis 自动重连次数
        'db'            => 0, // Redis 库
        'serialize'     => \EasySwoole\Redis\Config\RedisConfig::SERIALIZE_JSON, // 序列化类型，默认不序列化
        'packageMaxLength' => 1024 * 1024 * 2, // 允许操作的最大数据
        // 配置 Redis 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 100, // 设置 连接池最大数量
        'minObjectNum'  => 25, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],

'JWT' =>[
    'secretKey' => 'amd)4^&!@-#0$45c3TNaSl6*(S2_d1+9%',
    'issuer' => 'esdh', // 签发人
    'expire' => 864000,
],

    'ADMIN_JWT' =>[
        'secretKey' => 'o#3f*$^&d_S)8(02@32+GD6ys%!d3ASo-',
        'issuer' => 'esdh', // 签发人
        'expire' => 86400,
    ],

    // 有些接口的数据需要特殊处理
    'SPECIAL_INTERFACE' => [
        '/Api/Home/Index/index' => \App\Utility\Response\EmptyArrayToObject::class,
    ],

    // 需要单独开启的计划任务
    'CRONTAB_LIST' => [
//        'MoveVideoPictureCrontab', // 转移影视图片
    ],

    // 需要单独开启的队列任务
    'QUEUE_LIST' => [
  //      'MoveVideoPictureQueue' // 转移影视图片
    ],

// 需要单独开启的计划任务
'CRONTAB_LIST' => [
    'EncryptionImageCrontab', // 加密图片
],

// 需要单独开启的队列任务
'QUEUE_LIST' => [
    'EncryptionImageQueue' // 加密图片
],
// 需要单独开启的计划任务
'CRONTAB_LIST' => [
    'UploadXyzImageCrontab', // 加密图片
],

// 需要单独开启的队列任务
'QUEUE_LIST' => [
    'UploadXyzImageQueue' // 加密图片
],

];
