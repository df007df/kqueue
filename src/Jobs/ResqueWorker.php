<?php
/**
 * Created by PhpStorm.
 * User: df
 * Date: 17/7/22
 * Time: 22:13
 */

namespace KResque\Jobs;

use Resque_Worker;

class ResqueWorker extends Resque_Worker
{


    /**
     * @todo 正确获取执行的 queue
     * @return array
     */
    public static function getWorkerPids($queue = '')
    {
        $pids = [];
        $cmd = "ps -A -o pid,command | grep \"[r]esque/listen $queue\"";
        exec($cmd, $cmdOutput);
        foreach ($cmdOutput as $line) {
            list($pids[],) = explode(' ', trim($line), 2);
        }

        return $pids;
    }


    //平滑kill redis记录中步在运行的worker
    public static function pruneAllWorkers($queue = '')
    {

        $pids = self::getWorkerPids($queue);
        foreach ($pids as $pid) {
            self::killProcess($pid);
        }
    }


    public static function killProcess($pid)
    {
        posix_kill($pid, SIGQUIT);
    }


    public function workerPids()
    {

        return self::getWorkerPids();
    }

//
//    public function pruneDeadWorkers()
//    {
//        $workerPids = $this->workerPids();
//
//        //prd($workerPids);
//    }

}