任务队列
===============================

基本的任务队列组件，基于php-resque, 底层是redis实现.

INSTALL
-------------------
#### 添加composer.json
```
    "repositories": [
        {
            "type": "vcs",
            "url": "git@git.kuainiujinke.com:knjk/kqueue.git"
        }
    ],
    "require": {
        "knjk/kqueue": "dev-master"
    },
```

#### 执行composer install安装模块，vendor目录会多出vendor/knjk/kqueue，即表示安装成功

#### 配置文件说明


```
'components' => [

    'queue'  => [   //对应 command 的名称
        'class' => 'KQueue\QueueComponent',
        'redis' => [
            'hostname' => 'localhost',
            'port'     => 6379
        ],
        'app'   => [
            'id' => 'fk',  //应用唯一标示
        ],
        jobs'  => [
            [
                'class'           => 'Queue\Jobs\DemoJob',   //实际job类名
                'worker_count'    => 2,                      //通过supervisor配置开启的进程数
            ],
            [
                'class'           => 'Queue\Jobs\TestJob',
                'worker_count'    => 2,
            ]
        ]
    ]                                 
];
```

#### 代码说明
```
     
//job类说明

use KQueue\QueueJob;     //job 需要继承的基类

class TestJob extends QueueJob
{


    public $name;     //push队列时，传入参数的对应属性声明
    
    public $date;


    //job 执行函数
    public function execute($queue)
    {

        echo $dd;
        echo 'execute';
    }


    //队列失败后，多长时间后在重试此job
    public function getTtr()
    {
        return 10 * 60;
    }


    //判断是否继续重发此job
    //$attempt 已经重试的次数
    //本函数标示，重试次数大于5时，不在重试此job,并删除出队列
    public function canRetry($attempt, $error)
    {
        return ($attempt < 5);
    }

}

```

#### 发送队列
```

#发送一条job到队列中：
#第一个参数，job类名。第二个参数，传入的需要的参数，数组形式，对应好类的属性。

$msg = TestJob::push([
    'name' => rand(1,50),
    'date' => date('Y-m-d H:i:s')
]);

//返回队列id
$msg->hasMsgId();

//判断 job 是否在等待
$msg->isWaiting();

//判断 job 是否执行完成
$msg->isDone();

//详见代码

```

#### 简单命令说明
```
    #DemoJob 实际job对应的类文件名，保持完全一致
   
    #队列进程监听开始
    yii queue/listen DemoJob --verbose=1 --color=0    
    
    #关闭队列监听，请勿直接 kill pid.
    ./yii queue/resque/kill [CreditFetchJob]
    
```

#### 搭配 supervisor 使用

##### 因为此队列建议通过 supervisor 进行多进程管理，所以实际线上部署时，请使用 supervisor 进行管理。（supervisor使用请自行g）


