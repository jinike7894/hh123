<?php

namespace EasySwoole\EasySwoole;

use App\Crontab\Market\VipOrderExceedsTimeLimitCrontab;
use App\Crontab\Merchant\MerchantBillSettlementCrontab;
use App\Crontab\Navigation\ClearAdClickRecordCrontab;
use App\Crontab\Navigation\ChannelCostStatisticsCrontab;
use App\Crontab\Video\MoveShortVideoCrontab;
use App\Crontab\User\UserVIPHasExpiredCrontab;
use App\Crontab\Video\MoveVideoPictureCrontab;
use App\Crontab\Video\UploadXyzImageCrontab;
use App\Enum\RedisDb;
use App\Process\Video\MoveVideoPictureProcess;
use App\Process\Video\UploadXyzImageProcess;
use App\Queue\Video\MoveVideoPictureQueue;
use App\Queue\Video\UploadXyzImageQueue;
use App\Utility\LogHandler;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\FileWatcher\FileWatcher;
use EasySwoole\FileWatcher\WatchRule;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');

        ###### log begin ######
        // 注册自定义 `logger` 处理器
        \EasySwoole\Component\Di::getInstance()->set(\EasySwoole\EasySwoole\SysConst::LOGGER_HANDLER, \App\Utility\LogHandler::getInstance());
        // 或使用如下方式进行注册自定义 `logger` 处理器
        //\EasySwoole\EasySwoole\Logger::getInstance(new \App\Utility\LogHandler());
        ###### log end ######

        ###### 注册 mysql 连接池 begin ######
        // 连接池配置已经在配置文件中进行了配置
        $config = new \EasySwoole\ORM\Db\Config(Config::getInstance()->getConf('MYSQL'));
        DbManager::getInstance()->addConnection(new Connection($config));
        ###### 注册 mysql 连接池 end ######

        ###### 注册 redis 连接池 begin ######
        // 使用方法如下
        // $redis = \EasySwoole\RedisPool\RedisPool::defer();
        // $ret = $redis->setex('a', 600, 1);

        // 分别注册对应库的连接池
        // 默认缓存 0库 注意默认缓存0库是不能加 db key 的
        $redisConfigArray = Config::getInstance()->getConf('REDIS');
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray));
        // 权限验证 1库
        $redisConfigArray = Config::getInstance()->getConf('REDIS');
        $redisConfigArray['db'] = RedisDb::REDIS_DB_AUTH;
        $redisConfigArray['maxObjectNum'] = 5;
        $redisConfigArray['minObjectNum'] = 2;
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray), $redisConfigArray['db']);
        // SESSION及相关的不要清除的 2库
        $redisConfigArray = Config::getInstance()->getConf('REDIS');
        $redisConfigArray['db'] = RedisDb::REDIS_DB_SESSION;
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray), $redisConfigArray['db']);
        // 统计 3库
        $redisConfigArray = Config::getInstance()->getConf('REDIS');
        $redisConfigArray['db'] = RedisDb::REDIS_DB_STATISTIC;
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray), $redisConfigArray['db']);
        // 队列 4库
        $redisConfigArray = Config::getInstance()->getConf('REDIS');
        $redisConfigArray['db'] = RedisDb::REDIS_DB_QUEUE;
        \EasySwoole\RedisPool\RedisPool::getInstance()->register(new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray), $redisConfigArray['db']);
        ###### 注册 redis 连接池 end ######
    }

    public static function mainServerCreate(EventRegister $register)
    {
        if (Core::getInstance()->runMode() == 'dev') {
            ###### file watcher begin ######
            $watcher = new FileWatcher();
            $rule = new WatchRule(EASYSWOOLE_ROOT . '/App'); // 设置监控规则和监控目录
            $watcher->addRule($rule);
            $watcher->setOnChange(function () {
                Logger::getInstance()->info('file change ,reload!!!');
                ServerManager::getInstance()->getSwooleServer()->reload();
            });
            $watcher->attachServer(ServerManager::getInstance()->getSwooleServer());
            ###### file watcher end ######
        }

        ###### 注册 定时任务 start ######
        if (Config::getInstance()->getConf('ENABLE_CRONTAB')) {
            // 配置定时任务
            $crontabConfig = new \EasySwoole\Crontab\Config();

            // 1.设置执行定时任务的 socket 服务的 socket 文件存放的位置，默认值为 当前文件所在目录
            // 这里设置为框架的 Temp 目录
            $crontabConfig->setTempDir(EASYSWOOLE_TEMP_DIR);

            // 2.设置执行定时任务的 socket 服务的名称，默认值为 'EasySwoole'
            $crontabConfig->setServerName('EasySwoole');

            // 3.设置用来执行定时任务的 worker 进程数，默认值为 3
            $crontabConfig->setWorkerNum(3);

            // 4.设置定时任务执行出现异常的异常捕获回调
            $crontabConfig->setOnException(function (\Throwable $throwable) {
                // 定时任务执行发生异常时触发（如果未在定时任务类的 onException 中进行捕获异常则会触发此异常回调）
                LogHandler::getInstance()->logCustomFile($throwable->getMessage(), 'Crontab/Default', LogHandler::LOG_LEVEL_ERROR, 'error');
            });

            // 创建定时任务实例
            $crontab = \EasySwoole\EasySwoole\Crontab\Crontab::getInstance($crontabConfig);

            // 商户账单结算
            $crontab->register(new MerchantBillSettlementCrontab());
            // 清除广告点击记录
            $crontab->register(new ClearAdClickRecordCrontab());
            // 订单过期任务
            $crontab->register(new VipOrderExceedsTimeLimitCrontab());
            // VIP会员过期
            $crontab->register(new UserVIPHasExpiredCrontab());
            
            // 统计渠道成本
            $crontab->register(new ChannelCostStatisticsCrontab());
            
            

            $crontabList = Config::getInstance()->getConf('CRONTAB_LIST');
        }
        ###### 注册 定时任务 end ######

        ###### 注册 队列 start ######

        $queueList = Config::getInstance()->getConf('QUEUE_LIST');
        if ($queueList) {
            $redisConfigArray = Config::getInstance()->getConf('REDIS');
            $redisConfigArray['db'] = RedisDb::REDIS_DB_QUEUE;
            $redisConfig = new \EasySwoole\Redis\Config\RedisConfig($redisConfigArray);

        }

        ###### 注册 队列 end ######
    }
}