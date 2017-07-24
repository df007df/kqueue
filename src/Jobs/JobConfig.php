<?php

namespace KResque\Jobs;

use Resque;

/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class JobConfig
{


    public $job = '';

    public $worker_count = 1;

    public $queue = 'app_name';

    public $max_queue_retry = 500;

    public $log_path = '/tmp/kresque_logs/';

    /**
     * @var int 0|1|2
     */
    public $log_level = 0;

    public $interval = 5;

    public $pid_file = '';

    /**
     * @var BaseJob
     */
    protected $_job = null;


    public function __construct($config = [])
    {
        foreach ($config as $key => $val) {
            if (isset($this->{$key})) {
                $this->{$key} = $val;
            }
        }
        //new job
        $this->_job = new $this->job;
    }


    public function getQueue()
    {
        return $this->queue ? $this->queue . ':' . $this->_job->getQueueChannel() : $this->_job->getQueueChannel();
    }


    public function getWorkerCount()
    {
        return $this->worker_count;
    }


    public function getLogLevel()
    {
        return $this->log_level;
    }


    public function getInterval()
    {
        return $this->interval;
    }


    public function getPidFile()
    {
        return $this->pid_file;
    }


    public function setBackend()
    {
        Resque::setBackend('localhost:6379');
    }

}