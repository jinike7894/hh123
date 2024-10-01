<?php

namespace App\Utility;

use EasySwoole\Component\Singleton;
use EasySwoole\Log\LoggerInterface;

class LogHandler implements LoggerInterface
{

    private $logDir;

    use Singleton;

    function __construct(string $logDir = null)
    {
        if (empty($logDir)) {
            $logDir = getcwd() . '/Log';
        }
        $this->logDir = $logDir;
    }

    /**
     * eg : \EasySwoole\EasySwoole\Logger::getInstance()->log('日志内容');
     * @param string|array|null $msg
     * @param int $logLevel
     * @param string $category
     * @return string
     */
    function log($msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'debug'): string
    {
        is_array($msg) && $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);

        $time = time();
        $date = date('Ymd', $time);
        $yearMonth = date('Ym', $time);
        $dateTime = date('Y-m-d H:i:s', $time);
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir . "/{$yearMonth}/log_{$date}.log";

        // file_put_contents 无法自动创建目录
        $dirPath = substr($filePath, 0, strrpos($filePath, '/'));
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $str = "[{$dateTime}][{$category}][{$levelStr}]:[{$msg}]" . PHP_EOL;
        file_put_contents($filePath, "{$str}", FILE_APPEND | LOCK_EX);
        return $str;
    }

    /**
     * 如果想使用自定义的目录创建对应功能的日志使用这个方法。
     * 特别注意：所有的日志都是以年月日区分的
     * eg : \App\Utility\LogHandler::getInstance()->logCustomFile('日志内容', 'Market/buy');
     * @param string|array|null $msg
     * @param string $filename
     * @param int $logLevel
     * @param string $category
     * @return string
     */
    function logCustomFile($msg, string $filename = 'log', int $logLevel = self::LOG_LEVEL_DEBUG, string $category = 'debug'): string
    {
        is_array($msg) && $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        $filename = ltrim($filename, '/');

        $time = time();
        $date = date('Ymd', $time);
        $yearMonth = date('Ym', $time);
        $dateTime = date('Y-m-d H:i:s', $time);
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir . "/{$yearMonth}/{$filename}_{$date}.log";

        // file_put_contents 无法自动创建目录
        $dirPath = substr($filePath, 0, strrpos($filePath, '/'));
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0755, true);
        }

        $str = "[{$dateTime}][{$category}][{$levelStr}]:[{$msg}]" . PHP_EOL;
        file_put_contents($filePath, "{$str}", FILE_APPEND | LOCK_EX);
        $this->console($msg, $logLevel, $category);
        return $str;
    }

    function console(?string $msg, int $logLevel = self::LOG_LEVEL_INFO, string $category = 'console')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $temp = "[{$date}][{$category}][{$levelStr}]:[{$msg}]" . PHP_EOL;
        fwrite(STDOUT, $temp);
    }

    private function levelMap(int $level)
    {
        switch ($level) {
            case self::LOG_LEVEL_DEBUG:
                return 'debug';
            case self::LOG_LEVEL_INFO:
                return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }
}