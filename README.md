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
            "url": "git@git.fond.io:b/workflow.git"
        }
    ],
    "require": {
        "b/workflow" : "dev-master",
    },
```

2. 执行composer install安装模块，vendor目录会多出vendor/b/workflow目录，即表示安装成功
3. 在项目根目录执行./yii migrate -p=vendor/b/workflow/src/migrations。新建工作流数据库
4. 在项目配置文件加入模块配置。例如，在backend/config/main.php里添加如下配置：
```
    'modules' => [
        'workflow' => [
            'class' => 'workflow\Module',
        ],
    ],
```

5. 访问路由，http://host/workflow, host为项目域名

6. 添加component,在yii2里可以直接通过\Yii::$app->workflow访问workflow\WorkflowFacade门面类
```
    'components' => [
        'workflow' => [
            'class' => 'workflow\WorkflowFacade',
        ],
    ],
```
