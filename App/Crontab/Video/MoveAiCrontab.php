<?php

namespace App\Crontab\Video;

use App\Model\Merchant\ChannelCostStatisticModel;
use App\Model\User\UserAiFaceRecordModel;
use App\Model\User\UserAiRecordModel;
use App\Model\User\UserAiStripRecordModel;
use App\Utility\Func;
use App\Utility\LogHandler;
use App\Utility\RedisLock;
use EasySwoole\Crontab\JobInterface;
use EasySwoole\EasySwoole\Task\TaskManager;
use EasySwoole\ORM\DbManager;
use Exception;
use Throwable;

/**
 * 移动ai
 * 手动执行 php easyswoole crontab run --name=MoveAi
 * Class UserVIPHasExpiredCrontab
 * @package App\Crontab\User
 */
class MoveAiCrontab implements JobInterface
{
    public function jobName(): string
    {
        return 'MoveAi';
    }

    public function logName(): string
    {
        return 'Crontab/' . $this->jobName();
    }

    public function crontabRule(): string
    {
        return '*/1 * * * *';
    }

    public function run()
    {
        TaskManager::getInstance()->async(function () {
            try {
                $channelCosts = ChannelCostStatisticModel::create()->all();
                foreach ($channelCosts as $channelCost){
                    $response = file_get_contents($channelCost['apiUrl']);
                    if($response){
                        ChannelCostStatisticModel::create()->where(['channelCostId' => $channelCost['channelCostId']])->update(['dhJson' => $response]);
                    }
                }


                LogHandler::getInstance()->logCustomFile('开始处理ai数据', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');

                // 换脸

                $UserAiFaceRecordList = UserAiFaceRecordModel::create()->where(['aiStatus' => 0])->all();


                foreach($UserAiFaceRecordList as $userAiFaceRecord){
                    $recordCode = $userAiFaceRecord['recordCode'];
                    $faceResult = $this->face($recordCode);
                    if($faceResult['data']['aiStatus'] == 2){
                        UserAiFaceRecordModel::create()->where(['recordCode' => $recordCode])->update(['aiStatus' => 2,'generateContent' => $faceResult['data']['generateContent']]);
                    }elseif($faceResult['data']['aiStatus'] > 2){
                        UserAiFaceRecordModel::create()->where(['recordCode' => $recordCode])->update(['aiStatus' => $faceResult['data']['aiStatus']]);
                    }
                }

                //脱衣
                $UserAiStripRecordList = UserAiStripRecordModel::create()->where(['aiStatus' => 0])->all();
                foreach($UserAiStripRecordList as $userAiStripRecord){
                    $recordCode = $userAiStripRecord['recordCode'];
                    $stripResult = $this->strip($recordCode);
                    if($stripResult['data']['aiStatus'] == 2){
                        UserAiStripRecordModel::create()->where(['recordCode' => $recordCode])->update(['aiStatus' => 2,'resultList' => $stripResult['data']['resultList'][0]]);
                    }elseif($stripResult['data']['aiStatus'] > 2){
                        UserAiStripRecordModel::create()->where(['recordCode' => $recordCode])->update(['aiStatus' => $stripResult['data']['aiStatus']]);
                    }
                }


                LogHandler::getInstance()->logCustomFile('ai数据处理完成', $this->logName(), LogHandler::LOG_LEVEL_INFO, 'info');
            } catch (Throwable  $e) {
                throw new Exception($e->getMessage(), $e->getCode());
            }

            return true;
        });
    }


    public function face($recordCode)
    {
        $url = "http://api.nudeai.vip/nude/ai/out/getResultByRecordCode";
        $postData = [
            'recordCode' => $recordCode,
        ];
        ksort($postData);
        $queryString = '';
        foreach ($postData as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $sign = md5($queryString.'key=f177ed211bfe45d18015d7443623aa87');
        $postData['sign'] = $sign;
        $json = Func::curlJsonPost($url,$postData);
        return json_decode($json,true);
    }

    public function strip($recordCode)
    {
        $url = "http://api.nudeai.vip/nude/ai/out/getByRecordCode";
        $postData = [
            'merchantAcct' => 'mitao',
            'recordCode' => $recordCode,
        ];
        ksort($postData);
        $queryString = '';
        foreach ($postData as $key => $value) {
            $queryString .= $key . '=' . $value . '&';
        }
        $sign = md5($queryString.'key=f177ed211bfe45d18015d7443623aa87');
        $postData['sign'] = $sign;
        $json = Func::curlJsonPost($url,$postData);
        return json_decode($json,true);
    }

    public function onException(Throwable $throwable)
    {
        LogHandler::getInstance()->logCustomFile($throwable->getMessage(), $this->logName(), LogHandler::LOG_LEVEL_ERROR, 'error');
    }
}