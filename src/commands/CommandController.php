<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 16:01
 */

namespace KQueue\commands;

use Yii;
use yii\queue\redis\Command;
use yii\queue\cli\Command as CliCommand;
use yii\queue\redis\InfoAction;
use KQueue\QueueComponent;

class CommandController extends CliCommand
{


    /**
     * @var QueueComponent
     */
    public $queue;

    /**
     * @var string
     */
    public $defaultAction = 'info';


    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'info' => InfoAction::class,
        ];
    }


    /**
     * 只跑一个job
     *
     * @param $job
     *
     * @throws \Exception
     */
    public function actionRun($job)
    {
        $this->queue->filterWorkerQueue($job);
        $this->queue->run();
    }


    /**
     * 监听worker
     *
     * @param $job
     * @param int $wait
     *
     * @throws \Exception
     */
    public function actionListen($job, $wait = 3)
    {

        $this->queue->filterWorkerQueue($job);
        $this->queue->listen($wait);
    }


    /**
     * Executes a job.
     *
     * @param string|null $id of a message
     * @param int $ttr time to reserve
     * @param int $attempt number
     *
     * @return int exit code
     */
    public function actionExec($id, $ttr, $attempt)
    {
        //$this->queue->filterWorkerQueue($job);
        $message = file_get_contents('php://stdin');
        $this->_filterExec($message);

        if ($this->queue->execute($id, $message, $ttr, $attempt)) {
            return self::EXIT_CODE_NORMAL;
        } else {
            return self::EXIT_CODE_ERROR;
        }
    }


    /**
     * 过滤出当前worker
     * @param string $message
     *
     * @throws \Exception
     */
    protected function _filterExec($message = '')
    {

        if (!empty($message)) {
            $messageInfo = json_decode($message, true);

            if (isset($messageInfo[$this->queue->serializer->classKey])) {
                $className = $messageInfo[$this->queue->serializer->classKey];
                $workerId = $className::getId();
                $this->queue->filterWorkerQueue($workerId);
            }
        }
    }

}