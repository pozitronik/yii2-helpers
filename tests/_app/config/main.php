<?php /** @noinspection UsingInclusionReturnValueInspection */
declare(strict_types = 1);

use app\models\Users;
use app\modules\dummy\DummyModule;
use yii\log\FileTarget;
use yii\caching\DummyCache;
use yii\web\AssetManager;
use yii\web\ErrorHandler;

$db = require __DIR__.'/db.php';

$config = [
	'id' => 'basic',
	'basePath' => dirname(__DIR__),
	'bootstrap' => ['log'],
	'aliases' => [
		'@vendor' => './vendor',
		'@bower' => '@vendor/bower-asset',
		'@npm' => '@vendor/npm-asset',
		'@tests' => './tests'
	],
	'modules' => [
		'dummy' => [
			'class' => DummyModule::class,
			'params' => [
				'version' => '3.0'
			]
		]
	],
	'components' => [
		'request' => [
			'cookieValidationKey' => 'testKey',
		],
		'cache' => [
			'class' => DummyCache::class,
		],
		'user' => [
			'identityClass' => Users::class,
			'enableAutoLogin' => true,
		],
		'errorHandler' => [
			'class' => ErrorHandler::class,
			'errorAction' => 'site/error',
		],
		'log' => [
			'traceLevel' => YII_DEBUG?3:0,
			'targets' => [
				[
					'class' => FileTarget::class,
					'levels' => ['error', 'warning'],
				],
			],
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'rules' => [
			],
		],
		'assetManager' => [
			'class' => AssetManager::class,
			'basePath' => '@app/assets'
		],
		'db' => $db
	]
];

return $config;