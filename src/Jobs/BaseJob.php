<?php

namespace KResque\Jobs;

use Resque;
use Resque_Event;

/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
abstract class BaseJob
{


    public $args = [];

    public $queue;

    public $job;


    abstract function perform();


    /**
     *
     * @param array $params [exception, job]
     */
    public static function onFailure($params = [])
    {
    }


    public function setUp()
    {
        $className = get_called_class();
        Resque_Event::listen('onFailure', [$className, 'onFailure']);
    }


    public function tearDown()
    {
        echo 'tearDown';
    }


    public function dequeue()
    {
    }


    public function status()
    {
    }


    public function getArg($key = '*', $default = null)
    {

        if ($key == '*') {
            return $this->args;
        } else {
            return isset($this->args[$key]) ? $this->args[$key] : $default;
        }
    }


    public static function getConfig($params = [])
    {
        return array_merge([
            'class' => get_called_class(),
            'queue' => static::getQueueChannel(),
        ], $params);
    }


    /**
     * 获取队列名
     * @return mixed
     */
    public static function getQueueChannel()
    {
        $className = get_called_class();
        $classNames = explode('\\', $className);

        return array_pop($classNames);
    }


    //发送队列信息
    public static function enqueue($args = [])
    {

        $config = static::getConfig();

        return Resque::enqueue($config['queue'], $config['class'], $args);
    }
}