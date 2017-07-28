<?php

namespace KQueue;

use yii\queue\ErrorEvent;
use yii\queue\redis\Queue;
use yii\queue\serializers\JsonSerializer;
use yii\queue\LogBehavior;
use KQueue\Exceptions\TemporaryUnprocessableJobException;
use KQueue\commands\CommandController;
use Exception;

/**
 * yii2-queue
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class QueueComponent extends Queue
{


    public $jobs = [];

    public $app = [];

    protected static $_workerRedis = [];

    public $redis = [
        'hostname' => 'localhost',
        'port'     => 6379
    ];

    public static $channelBase = '';

    /**
     * @var string
     */
    public $channel = 'queue';

    public $serializer = JsonSerializer::class;

    public $commandClass = CommandController::class;


    public function init()
    {
        parent::init();

        static::$channelBase = $this->app['id'];

        $this->attachBehavior('log', LogBehavior::className());
        $this->on(Queue::EVENT_AFTER_ERROR, [$this, 'eventAfterError']);
    }


    /**
     * @param ErrorEvent $event
     */
    public function eventAfterError($event)
    {

        $job = $event->job;

        if ($event->error instanceof Exception) {
            $queue = $event->sender;
            //$queue->delay(7200)->push($job);
        }
    }


    /**
     * 查找当前worker的queue
     *
     * @param $worker
     *
     * @throws Exception
     */
    public function filterWorkerQueue($worker)
    {

        $find = false;
        foreach ($this->jobs as $job) {
            $className = $job['class'];
            $workerName = $className::getId();

            if ($workerName == $worker) {
                $this->channel = static::$channelBase . ':' . $workerName;
                $find = true;
                break;
            }
        }

        if (!$find) {
            throw new Exception("queue worker:$worker is not found!");
        }
    }
}