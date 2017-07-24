<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 16:01
 */

namespace KResque\commands;

use NQueue\Jobs\RunWorker;
use Queue\Jobs\DemoJob;
use yii\console\Controller;

class QueueController extends Controller
{


    public function actionPush()
    {
        $result = DemoJob::enqueue([
            'name' => 'gogo',
            'date' => date('Y-m-d H:i:s'),
        ]);


        prd($result);
    }


    public function actionListen()
    {



    }

}