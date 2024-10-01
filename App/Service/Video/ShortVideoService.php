<?php

namespace App\Service\Video;

use App\Enum\ConfigKey\OssConfigKey;
use App\Enum\RedisDb;
use App\Enum\Upload;
use App\Model\Common\ConfigModel;
use App\Model\Video\ShortVideoModel;
use App\RedisKey\Video\ShortVideoKey;
use App\Service\Oss\Aws\MoveLocalPicture;
use App\Utility\Func;
use EasySwoole\Component\Singleton;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\Http\Message\Status;
use EasySwoole\Mysqli\QueryBuilder;
use EasySwoole\ORM\DbManager;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Utility\File;
use Exception;
use Throwable;

class ShortVideoService
{
    use Singleton;

    public function addShortVideo($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $shortVideo = ShortVideoModel::create($data);
            $vodId = $shortVideo->save();
            if ($vodId === false) {
                throw new Exception('短视频添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();

            if ($shortVideo->vodPlayUrl && empty($shortVideo->vodPic)) {
                $this->generateCoverAsync($shortVideo);
            }

        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return $vodId;
    }

    public function editShortVideo($data)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $shortVideo = ShortVideoModel::create()->get([
                'vodId' => $data['vodId'],
                'status' => [ShortVideoModel::STATE_DELETED, '>'],
            ]);

            if (!$shortVideo) {
                throw new Exception('无效的短视频id', Status::CODE_BAD_REQUEST);
            }

            $result = $shortVideo->update($data);

            if ($result === false) {
                throw new Exception('短视频修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            DbManager::getInstance()->commitWithCount();

            if ($shortVideo->vodPlayUrl && empty($shortVideo->vodPic)) {
                $this->generateCoverAsync($shortVideo);
            }

        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 通过视频地址生成封面
     * @param ShortVideoModel $shortVideo
     * @return array
     * @throws Exception
     */
    public function generateCover(ShortVideoModel $shortVideo)
    {
        try {
            if (empty($shortVideo->vodPlayUrl)) {
                throw new Exception('没有对应的视频地址', Status::CODE_BAD_REQUEST);
            }

            // 这里可以使用 Func::CreateGuid()
            // 不过用id可以避免处理旧图
            $fileName = $shortVideo->vodId . '.jpg';
            $path = Upload::getImageDatePath(Upload::TYPE_VIDEO);
            $dirPath = Func::getPublicPath() . DIRECTORY_SEPARATOR . $path;

            File::createDirectory($dirPath);
            $fullFileName = $dirPath . DIRECTORY_SEPARATOR . $fileName;

            $result = shell_exec("ffmpeg -i {$shortVideo->vodPlayUrl} -y -f mjpeg -ss 4 -t 0.01 {$fullFileName}");
            if ($result === false) {
                throw new Exception('生成视频封面失败', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            // 这里有2种分支
            // 1.当s3开启的情况下将图片转移到s3
            // 2.s3未开启则直接进行数据库修改
            $enabled = ConfigModel::create()->getConfigValue(OssConfigKey::AWS_S3_ENABLED);
            if ($enabled) {
                $moveResult = MoveLocalPicture::getInstance()->move($fullFileName, Upload::TYPE_VIDEO, $shortVideo->createTime);
                $shortVideo->fileType = ShortVideoModel::FILE_TYPE_AWS_S3;
                $shortVideo->vodPic = $moveResult['path'];
            } else {
                $shortVideo->fileType = ShortVideoModel::FILE_TYPE_UP;
                $shortVideo->vodPic = DIRECTORY_SEPARATOR . $path . DIRECTORY_SEPARATOR . $fileName;
            }

            $result = $shortVideo->update();
            if ($result === false) {
                throw new Exception('短视频修改出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

        } catch (Throwable  $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return [
            'fileType' => $shortVideo->fileType,
            'path' => $shortVideo->vodPic,
        ];
    }

    /**
     * 通过视频地址生成封面（异步）
     * @param ShortVideoModel $shortVideo
     */
    public function generateCoverAsync(ShortVideoModel $shortVideo)
    {
        TaskManager::getInstance()->async(function () use ($shortVideo) {
            $this->generateCover($shortVideo);
        });
    }

    /**
     * 是否已经喜欢(存在Key)
     * @param $userId
     * @param $vodId
     * @return false|string
     */
    public function isAlreadyLike($userId, $vodId)
    {
        $key = ShortVideoKey::shortVideoLike($userId, $vodId);
        $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);
        return $redis->exists($key);
    }

    /**
     * 点赞
     * @param $userId
     * @param $vodId
     * @return bool
     * @throws Throwable
     */
    public function like($userId, $vodId)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $result = ShortVideoModel::create()
                ->where(['vodId' => $vodId])
                ->update([
                    'realLikeCount' => QueryBuilder::inc(),
                ]);

            if ($result === false) {
                throw new Exception('短视频点赞添加出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $key = ShortVideoKey::shortVideoLike($userId, $vodId);
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);

            // 3600秒内同一用户点赞只计算一次
            $redis->setEx($key, 3600, 1);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 取消点赞
     * @param $userId
     * @param $vodId
     * @return bool
     * @throws Throwable
     */
    public function dislike($userId, $vodId)
    {
        try {
            DbManager::getInstance()->startTransactionWithCount();

            $result = ShortVideoModel::create()
                ->where(['vodId' => $vodId])
                ->update([
                    'realLikeCount' => QueryBuilder::dec(),
                ]);

            if ($result === false) {
                throw new Exception('短视频点赞取消出错', Status::CODE_INTERNAL_SERVER_ERROR);
            }

            $key = ShortVideoKey::shortVideoLike($userId, $vodId);
            $redis = RedisPool::defer(RedisDb::REDIS_DB_STATISTIC);

            // 取消点赞则删除缓存键，以便再次点赞。
            $redis->del($key);

            DbManager::getInstance()->commitWithCount();
        } catch (Throwable  $e) {
            DbManager::getInstance()->rollbackWithCount();
            throw new Exception($e->getMessage(), $e->getCode());
        }

        return true;
    }

    /**
     * 记录搜索热词
     * @param $word
     */
    public function setHotWords($word)
    {

        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $redis->zInCrBy(ShortVideoKey::hotWord(), 1, $word);
        $redis->expire(ShortVideoKey::hotWord(), 86400);
    }

    /**
     * 获取搜索热词
     * @return array
     */
    public function getHotWords()
    {
        $redis = RedisPool::defer(RedisDb::REDIS_DB_QUEUE);
        $words = $redis->zRevRange(ShortVideoKey::hotWord(), 0, 20, true);
        return array_keys($words);
    }
}