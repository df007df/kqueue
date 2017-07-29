<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace KQueue\commands;

use KQueue\SupervisorHelper;
use yii\queue\cli\Action;
use KQueue\QueueComponent;

/**
 * SuperAction
 */
class SuperAction extends Action
{


    /**
     * @var QueueComponent
     */
    public $queue;


    /**
     * 管理 supervisor 配置
     *
     * @param string $superPath supervisor加载的配置目录
     * @param string $suffix 配置文件后缀
     * @param string $user 执行脚本的用户
     */
    public function run($superPath, $suffix = 'config', $user = 'nginx')
    {

        $appId = $this->queue->app['id'];
        $jobs = $this->queue->jobs;

        foreach ($jobs as $jobConfig) {
            $model = SupervisorHelper::startJob($appId, $jobConfig);
            $model->user = $user;

            if ($this->controller->save) {
                $model->saveConfig($superPath, $suffix);
            } else {
                echo $model->getConfigContent() . "\r\n";
                echo $model->getSaveConfigFile($superPath, $suffix) . "\r\n\r\n";
            }
        }
    }
}