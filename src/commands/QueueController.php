<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 16:01
 */

namespace KResque\commands;

use yii\console\Controller;
use Yii;

class QueueController extends Controller
{


    public function getResque()
    {
        return Yii::$app->resque;
    }


    /**
     * @param $queue
     */
    public function actionListen($queue)
    {

        return $this->getResque()->listen($queue);
    }


    /**
     * @param $queue
     */
    public function actionListenTest($queue)
    {

        return $this->getResque()->listen($queue, true);
    }


    public function actionInfo()
    {

        $info = $this->getResque()->info();

        prd($info);
    }


    public function actionKill($queue = '')
    {

        $info = $this->getResque()->kill($queue);
    }

}