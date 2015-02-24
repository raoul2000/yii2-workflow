<?php
/**
 * Application configuration for unit tests
 */
$db = require(__DIR__ . '/db.php');

return [
    'id' => 'basic',
    'basePath' => realpath(__DIR__ . '/../../../'),
    'bootstrap' => ['log'],
    'components' => [

        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
					'logFile' => __DIR__ . '/../_output/yii.log'
                ],
            ],
        ],
        'db' => $db
    ]
];
