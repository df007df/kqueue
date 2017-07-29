<?php
namespace KQueue;

use Yii;
use yii\base\Model;
use yii\helpers\Inflector;
use Exception;

/**
 * SupervisorHelper
 * User: df
 * Date: 17/7/14
 * Time: 14:43
 */
class SupervisorHelper extends Model
{


    public $program;

    public $numprocs = 1;

    public $directory;

    public $command;

    public $autostart = true;

    public $startsecs = 5;

    public $autorestart = true;

    public $startretries = 5;

    public $user = '';

    public $redirect_stderr = true;

    public $stdout_logfile_maxbytes = '20MB';

    public $stdout_logfile_backups = 20;

    public $stdout_logfile = 'knjk_deploy/supervisor/logs';


    public static function startJob($appId, $jobConfig = [])
    {

        $config = new static();
        $className = $jobConfig['class'];
        $classKey = Inflector::camelize($className);
        $classId = $className::getId();

        $basePath = Yii::$app->basePath;
        if (file_exists($basePath . '/yii')) {

            $config->directory = $basePath;
        } else {

            do {
                $info = pathinfo($basePath);
                $basePath = $info['dirname'];
                $find = file_exists($basePath . '/yii');
                $config->directory = $basePath;
            } while (!$find);
        }

        if (empty($config->directory)) {
            throw new Exception('command yii directory is not found!');
        }

        $config->command = "php yii queue/listen {$classId} --verbose=1 --color=0";

        $config->program = $appId . '_kqueue_worker_' . $classKey;
        $config->numprocs = $jobConfig['worker_count'];

        $config->stdout_logfile = $config->getLogsFile($classKey);
        $config->user = $config->getUser();

        return $config;
    }


    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @param string $classKey
     * @return string
     */
    public function getLogsFile($classKey)
    {
        $dir = $this->directory . DIRECTORY_SEPARATOR . $this->stdout_logfile;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir . DIRECTORY_SEPARATOR . "kqueue_$classKey.log";
    }


    /**
     * @param $superPath
     * @param $file
     *
     * @return string
     */
    public function getConfigFile($superPath, $file)
    {

        return $superPath . DIRECTORY_SEPARATOR . $file;
    }


    /**
     * @param string $suffix
     *
     * @return string
     */
    public function getConfigFileName($suffix)
    {
        return $this->program . '.' . $suffix;
    }


    public function getConfigContent()
    {

        $tmp = <<<TMP
[program:{$this->program}]
process_name=%(program_name)s_%(process_num)02d
directory = {$this->directory} ;程序的启动目录
command = {$this->command}  ; 启动命令，可以看出与手动在命令行启动的命令是一样的
numprocs = {$this->numprocs} ; 启动的进程数
autostart =  {$this->autostart}     ; 在 supervisord 启动的时候也自动启动
startsecs =  {$this->startsecs}        ; 启动 5 秒后没有异常退出，就当作已经正常启动了
autorestart =  {$this->autorestart}   ; 程序异常退出后自动重启
startretries =  {$this->startretries}     ; 启动失败自动重试次数，默认是 3
user =  {$this->user}          ; 用哪个用户启动
redirect_stderr =  {$this->redirect_stderr}  ; 把 stderr 重定向到 stdout，默认 false
stopsignal = TERM
stdout_logfile_maxbytes =  {$this->stdout_logfile_maxbytes}  ; stdout 日志文件大小，默认 50MB
stdout_logfile_backups =  {$this->stdout_logfile_backups}     ; stdout 日志文件备份数
;stdout 日志文件，需要注意当指定目录不存在时无法正常启动，所以需要手动创建目录（supervisord 会自动创建日志文件）
stdout_logfile =  {$this->stdout_logfile}
TMP;

        return $tmp;
    }


    public function getSaveConfigFile($superPath, $suffix)
    {
        return $this->getConfigFile($superPath, $this->getConfigFileName($suffix));
    }


    public function saveConfig($superPath, $suffix = 'config')
    {

        $filePath = $this->getSaveConfigFile($superPath, $suffix);

        return file_put_contents($filePath, $this->getConfigContent());
    }
}
