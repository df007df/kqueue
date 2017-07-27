任务队列
===============================

基本的任务队列组件，基于php-resque, 底层是redis.
### 推荐安装redis管理工具 PHPredisadmin

INSTALL
-------------------

1. 安装redis
```
    brew install redis
```


2. 添加composer.json
```
    "repositories": [
        {
            "type": "vcs",
            "url": "https://git.kuainiujinke.com/knjk/KResque"
        }
    ],
    "require": {
        "knjk/KResque" : "dev-master",
    },
```

3. 执行composer install安装模块，vendor目录会多出vendor/knjk/KResque目录，即表示安装成功
4. 配置文件说明
```

        $config = [

            'app'  => [
                'host' => 'localhost:6379',
                'job'  => [
                    'queue' => 'app_fk_name',
                ]

            ],
            'jobs' => [
                [
                    'job'             => 'Queue\Jobs\DemoJob', //具体实现的类型
                    'worker_count'    => 2,                    //开启 worker 的数量
                    'max_queue_retry' => 500,                  //
                ],
                [
                    'job'             => 'Queue\Jobs\TestJob',
                    'worker_count'    => 2,
                    'max_queue_retry' => 500,
                ]
            ]

        ];
```

5. 基本使用说明
```

    #发送一条任务到队列中：
    #第一个参数，job类名。第二个参数，传入的需要的参数，没个数限制（不能为对象）

    $result = Yii::$app->resque->enqueue("Queue\\Jobs\\DemoJob", [
      'name' => 'test_name',
      'date' => date('Y-m-d H:i:s'),
    ]);
```

6. job类说明
```
       <?php
        /**
         * Created by PhpStorm.
         * User: df
         * Date: 17/7/14
         * Time: 15:55
         */
        
        namespace Queue\Jobs;
        
        use KResque\Jobs\BaseJob;
        
        class DemoJob extends BaseJob  //注意继承此类
        {
        
        
            //job 执行之前执行的操作
            public function setUp()
            {
        
                echo 'setUp';
            }
        
            //job 成功执行之后执行的操作
            public function tearDown()
            {
                echo 'tearDown';
            }
        
            //job 实际的执行逻辑
            public function perform()
            {
                //$this->getArg('key'), 获取传入的参数
                echo $this->getArg('name');
        
                sleep(1);
            }
        
        }

```