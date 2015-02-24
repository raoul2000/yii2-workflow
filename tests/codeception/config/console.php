<?php

Yii::setAlias('@tests', dirname(__DIR__) . '/tests');

$db = require(__DIR__ . '/db.php');
return [
    'id' => 'basic-console',
    'basePath' => YII_APP_BASE_PATH,
    'controllerNamespace' => 'app\commands',
    'extensions' => require(YII_APP_BASE_PATH . '/vendor/yiisoft/extensions.php'),
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
					'logFile' => '@tests/codeception/_output/.tests.log'
                ],
            ],
        ],
        'db' => $db,
    ]
];
