<?php
namespace KQueue;

use Yii;
use yii\queue\RetryableJob;
use yii\base\Model;

/**
 * Job基类
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class QueueJob extends Model implements RetryableJob
{


    /**
     * @var string 任务ID
     */
    private $_msgId = '';


    /**
     * 逻辑执行
     *
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
    }


    /**
     * 队列等待时间
     * @return int
     */
    public function getTtr()
    {
        return 15 * 60;
    }


    /**
     * 是否重试
     *
     * @param int $attempt
     * @param \Exception $error
     *
     * @return bool
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < 10);
    }


    /**
     * @return QueueRedisComponent
     */
    public static function getQueue()
    {

        $queue = Yii::$app->queue;
        $worker = static::getId();
        $queue->filterWorkerQueue($worker);

        return $queue;
    }


    public static function getId()
    {
        $className = static::class;
        $class = explode('\\', $className);
        $workerName = array_pop($class);

        return $workerName;
    }


    /**
     * 发送队列
     *
     * @param array $params
     * @param int $time
     *
     * @return static
     */
    public static function push($params = [], $time = 0)
    {

        $model = new static($params);

        if ($time > 0) {
            $model->_msgId = static::getQueue()->delay($time)->push($model);
        } else {
            $model->_msgId = static::getQueue()->push($model);
        }

        return $model;
    }


    /**
     * @return string
     */
    public function getMsgId()
    {
        return $this->_msgId;
    }


    /**
     * @return bool
     */
    public function hasMsgId()
    {
        return !empty($this->getMsgId());
    }


    /**
     * @return bool
     */
    public function isWaiting()
    {
        return static::getQueue()->isWaiting($this->getMsgId());
    }


    /**
     * @return bool
     */
    public function isReserved()
    {
        return static::getQueue()->isReserved($this->getMsgId());
    }


    /**
     * @return bool
     */
    public function isDone()
    {
        return static::getQueue()->isDone($this->getMsgId());
    }

}
