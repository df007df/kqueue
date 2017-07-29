任务队列
===============================

基本的任务队列组件，基于yii-queue(底层支持多种消息队列，redis,mysql,beanstalkd 等), 此版本是redis的实现.

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
    
    #kill 进程，请自行查找pid 然后kill
    #此命令基本只是本地调试时使用，多进程管理请参考 supervisor
    #原生不支持多进程启动，我猜因为实际部署都会用 supervisor进行管理。那还是原生不要支持多进程吧，多此一举，增加复杂度了。
    
```

#### 搭配 supervisor 使用

##### 因为此队列建议通过 supervisor 进行多进程管理，所以实际线上部署时，请使用 supervisor 进行管理。（supervisor使用请自行g）

```

#生成项目队列的 supervisor 配置文件的工具

yii queue/super <superPath> [suffix] [user] [...options...]

- superPath (required): string
  supervisor加载的配置目录

- suffix: string (defaults to 'config')
  配置文件后缀

- user: string (defaults to 'nginx')
  执行脚本的用户
  
--save: boolean, 0 or 1 (defaults to 0)
  刷新supervisor配置  
  

#下面命令是mac, 使用时参数请自行替换。
  
#打印配置文件的内容，进行查看  
./yii queue/super /usr/local/etc/supervisor.d ini df

#刷新配置文件到指定目录
./yii queue/super /usr/local/etc/supervisor.d ini df --save



#启动 supervisord  mac版

supervisord -c /usr/local/etc/supervisord.ini


```

