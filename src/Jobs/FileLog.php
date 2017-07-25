<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/19
 * Time: 14:09
 */

namespace KResque\Jobs;

use Resque_Redis;
use Resque_Log;
use Psr\Log\LogLevel;

class FileLog extends Resque_Log
{


    protected $_file_out = null;

    /**
     * @var JobConfig
     */
    protected $_config = null;


    public function __construct($verbose = false)
    {
        parent::__construct($verbose);
    }


    public function setConfig(JobConfig $config)
    {
        $this->_config = $config;
    }


    public function getLogFile()
    {

        $prefix = Resque_Redis::getPrefix();

        $logPatchs = [
            $this->_config->getQueue(),
            'resque.log'
        ];

        if ($prefix) {
            array_unshift($logPatchs, trim($prefix, ':'));
        }

        $logFile = implode('.', $logPatchs);
        $logFile = rtrim($this->_config->log_path, '/') . "/$logFile";

        return $logFile;
    }


    protected function _getOut()
    {
        //prd($this->_config->log_path);
        if (!is_dir($this->_config->log_path)) {
            mkdir($this->_config->log_path);
        }

        $logFile = $this->getLogFile();

        if (!file_exists($logFile) || is_null($this->_file_out)) {
            $this->_file_out = fopen($logFile, 'a');
        }

        return $this->_file_out;
    }


    public function log($level, $message, array $context = [])
    {
        $pid = getmypid();
        if ($this->verbose) {

            $this->isFlock($this->_getOut(), function ($fp) use ($level, $pid, $message, $context) {

                fwrite(
                    $fp,
                    '[' . $level . '][' . $pid . '][' . strftime('%Y-%m-%d %T') . '] ' . $this->interpolate($message, $context) . PHP_EOL
                );
            });

            return;
        }

        if (!($level === LogLevel::INFO || $level === LogLevel::DEBUG)) {

            $this->isFlock($this->_getOut(), function ($fp) use ($level, $pid, $message, $context) {

                fwrite(
                    $fp,
                    '[' . $level . '][' . $pid . ']' . $this->interpolate($message, $context) . PHP_EOL
                );
            });
        }
    }


    public function isFlock($fp, $fun)
    {

        if (flock($fp, LOCK_EX)) {
            $fun($fp);
            flock($fp, LOCK_UN);
        }
    }

}