<?php

namespace KResque\Jobs;

use Resque;
use Resque_Log;
use Resque_Redis;
use Psr\Log\LogLevel;
use Exception;

/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class RunWorker
{


    /**
     * @var JobConfig
     */
    protected $_config = null;

    protected $_logger = null;


    /**
     * @param JobConfig $config
     * @param bool $test
     */
    public function __construct(JobConfig $config, $test = false)
    {
        $this->_config = $config;
    }


    public static function parseConfig($config = [])
    {

        $appConfig = $config['app'];
        Resque::setBackend($appConfig['host']);
        Resque_Redis::prefix($appConfig['id']);

        $jobs = $config['jobs'];

        foreach ($jobs as &$job) {
            $job = array_merge($appConfig['job'], $job);
        }

        return $jobs;
    }


    public static function getInfo()
    {

        $queues = Resque::queues();
        $prefix = Resque_Redis::getPrefix();

        $queuesLen = [];
        foreach ($queues as $queue) {
            $queueKey = "queue:$queue";
            $queuesLen[$queue] = Resque::redis()->llen($queueKey);
        }

        $statFailed = 'stat:failed';
        $statProcessed = 'stat:processed';
        $resqueFailed = 'failed';

        $workersKey = 'workers';
        $workersList = Resque::redis()->smembers($workersKey);

        $workers = [];
        foreach ($workersList as $worker) {

            $workerDataKey = "worker:$worker";
            $workers[$worker] = Resque::redis()->get($workerDataKey);
        }

        $info = [

            'queues'  => $queues,
            'queue'   => $queuesLen,
            'stat'    => [
                'failed'    => Resque::redis()->get($statFailed),
                'processed' => Resque::redis()->get($statProcessed),
            ],
            'workers' => $workers
            //'resque_failed' => Resque::redis()->llen($resqueFailed)

        ];

        return $info;
    }


    public static function getFailedList($page = 1, $limit = 25)
    {
        $resqueFailed = 'failed';

        //$config = self::parseConfig($config);

        $count = Resque::redis()->llen($resqueFailed);

        $stop = $page * $limit - 1;
        $start = $stop > $limit ? $stop - $limit : 0;

        $list = Resque::redis()->lrange($resqueFailed, $start, $stop);

        return $list;
    }


    /**
     * 根据配置执行 work
     * @param $configs
     * @param $queue
     *
     * @return null|RunWorker
     * @throws Exception
     */
    public static function runWithConfig($configs, $queue)
    {

        $worker = null;
        foreach ($configs as $config) {
            $jobClass = $config['job'];
            $queueChannel = $jobClass::getQueueChannel();

            if ($queueChannel == $queue) {
                $configModel = new JobConfig($config);
                $worker = new static($configModel);
                break;
            }
        }

        if (is_null($worker)) {
            throw new Exception("KResque queue:$queue is not found!");
        }

        return $worker;
    }


    /**
     * @param bool $test
     *
     * @return FileLog
     *
     */
    protected function _initLogger($test = false)
    {

        if ($test) {
            $log = new Resque_Log($this->_config->log_level ? true : false);
        } else {
            $log = new FileLog($this->_config->log_level ? true : false);
            $log->setConfig($this->_config);
        }

        return $log;
    }


    public function setPrefix($prefix)
    {

        $logger = $this->_logger;
        $logger->log(LogLevel::INFO, 'Prefix set to {prefix}', ['prefix' => $prefix]);
        Resque_Redis::prefix($prefix);
    }


    public function listen()
    {

        $this->_logger = $this->_initLogger();
        $count = $this->_config->getWorkerCount();
        $queue = $this->_config->getQueue();

        $logger = $this->_logger;

        $interval = $this->_config->getInterval();
        $pidFile = $this->_config->getPidFile();
        $BLOCKING = false;

        for ($i = 0; $i < $count; ++$i) {
            $pid = Resque::fork();
            if ($pid === false || $pid === -1) {
                $logger->log(LogLevel::EMERGENCY, 'Could not fork worker {count}', ['count' => $i]);
                die();
            } else {
                // Child, start the worker
                if (!$pid) {
                    $worker = new ResqueWorker($queue);
                    $worker->setLogger($logger);
                    $logger->log(LogLevel::NOTICE, 'Starting worker {worker}', ['worker' => $worker]);
                    $worker->work($interval, $BLOCKING);
                    break;
                }
            }
        }
    }


    public function listenTest()
    {

        $this->_config->log_level = 1;
        $this->_logger = $this->_initLogger(true);

        $queue = $this->_config->getQueue();
        $logger = $this->_logger;

        $interval = $this->_config->getInterval();
        $pidFile = $this->_config->getPidFile();
        $BLOCKING = false;

        $worker = new ResqueWorker($queue);
        $worker->setLogger($logger);

        if ($pidFile) {
            file_put_contents($pidFile, getmypid()) or
            die('Could not write PID information to ' . $pidFile);
        }

        $logger->log(LogLevel::NOTICE, 'Starting worker {worker}', ['worker' => $worker]);
        $worker->work($interval, $BLOCKING);
    }

}