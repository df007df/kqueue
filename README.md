任务队列
===============================

基本的任务队列组件，基于php-resque, 底层是redis.

INSTALL
-------------------
1. 添加composer.json
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

2. 执行composer install安装模块，vendor目录会多出vendor/knjk/KResque目录，即表示安装成功
//3. 在项目根目录执行./yii migrate -p=vendor/knjk/KResque/src/migrations。新建工作流数据库

3. 配置文件说明
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
