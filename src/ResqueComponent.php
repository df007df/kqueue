<?php

namespace KResque;

use KResque\Jobs\ResqueWorker;
use KResque\Jobs\RunWorker;
use Resque;
use Resque_Redis;
use Yii\base\Component;

/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class ResqueComponent extends Component
{


    /**
     * @var array
     */
    public $app = [];

    /**
     * @var array
     */
    public $jobs = [];


    public function init()
    {

        parent::init();

        self::initConfig($this->app);
    }


    public static function initConfig($appConfig = [])
    {

        Resque::setBackend($appConfig['host']);
        Resque_Redis::prefix($appConfig['id']);
    }


    public function getJobsConfig()
    {
        return $this->jobs;
    }


    /**
     * 发送队列信息
     * @param $className
     * @param array $args
     *
     * @return bool|string
     */
    public function enqueue($className, $args = [])
    {

        $config = $className::getConfig();

        return Resque::enqueue($config['queue'], $config['class'], $args);
    }


    public function listen($queue, $test = false)
    {
        $worker = RunWorker::runWithConfig($this->jobs, $queue);

        if ($test) {
            $worker->listenTest();
        } else {
            $worker->listen();
        }

    }

    public function info()
    {

        $info = RunWorker::getInfo();
        $list = RunWorker::getFailedList(1, 2);

        return [
            'info'   => $info,
            'failed' => $list,
        ];
    }


    public function kill($queue = '')
    {

        return ResqueWorker::pruneAllWorkers($queue);
    }

}