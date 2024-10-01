<?php

namespace App\Crontab\Video;

use App\Model\Video\ShortVideoModel;
use App\Model\Video\VideoModel;
use App\Utility\Func;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 从MacCms抖音分类视频移动到短视频表
 * 手动执行 php easyswoole crontab run --name=MoveShortVideo
 * Class UserVIPHasExpiredCrontab
 * @package App\Crontab\User
 */
class MoveShortVideoCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'MoveShortVideo';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '0 * * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            $lockKey = $this->jobName();
            $lockValue = RedisLock::lock($lockKey);

            try {
                LogHandler::getInstance()->logCustomFile('开始转移短视频数据', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                $cmsShortVideoList = VideoModel::create()
                    ->field(['vod_id', 'vod_name', 'vod_pic', 'vod_play_url','vod_score_num'])
                    ->where([
                        'type_id' => 62,
                        'vod_plot' => 0,
                        'vod_status' => VideoModel::STATE_NORMAL,
                    ])
                    ->limit(10)
                    ->all();
                if (!$cmsShortVideoList) {
                    LogHandler::getInstance()->logCustomFile('目前没有需要处理的短视频', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                    RedisLock::releaseLock($lockKey, $lockValue);
                    return true;
                }

                $dataList = [];
                $cmsIdList = [];
                foreach ($cmsShortVideoList as $cmsShortVideo) {
                    $vodName = mb_substr(trim($cmsShortVideo['vod_name']), 0, 60);
                    $data = [
                        'vodName' => $vodName,
                        'vodPic' => $cmsShortVideo['vod_pic'],
                        'vodPlayUrl' => explode('$', $cmsShortVideo['vod_play_url'])[1] ?? null,
                        'fileType' => ShortVideoModel::FILE_TYPE_URL,
                        'status' => ShortVideoModel::STATE_NORMAL,
                        'likeCount' => $cmsShortVideo['vod_score_num'],
                        // 'realLikeCount' => $cmsShortVideo['vod_score_num'],
                    ];
                    $dataList[] = $data;
                    $cmsIdList[] = $cmsShortVideo['vod_id'];
                }

                DbManager::getInstance()->startTransactionWithCount();

                $result = Func::insertAll(ShortVideoModel::create(), $dataList);

                if ($result === false) {
                    throw new Exception('转移短视频失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
                $result = VideoModel::create()
                    ->where([
                        'vod_id' => [$cmsIdList, 'IN']
                    ])
                    ->update([
                        'vod_plot' => 1,
                    ]);
                if ($result === false) {
                    throw new Exception('转移短视频修改失败', Status::CODE_INTERNAL_SERVER_ERROR);
                }
                DbManager::getInstance()->commitWithCount();
                LogHandler::getInstance()->logCustomFile('转移短视频处理完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
                RedisLock::releaseLock($lockKey, $lockValue);
            } catch (Throwable  $e) {
                DbManager::getInstance()->rollbackWithCount();
                RedisLock::releaseLock($lockKey, $lockValue);
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return true;
        });
    }

    public function onException(Throwable $throwable)
    {
        LogHandler::getInstance()->logCustomFile($throwable->getMessage(), $this->logName(), LogHandler::LOG_LEVEL_ERROR, 'error');
    }
}