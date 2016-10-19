<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SAREhub\Commons\Misc\Dsn;

return [
  'id' => 'test',
  'manager' => [
	'processService' => [
	  'runnerScript' => dirname(__DIR__).'/test_worker/testWorkerRunner.php',
	  'arguments' => [],
	  'workingDirectory' => './'
	],
	'commandService' => [
	  'commandOutput' => [
		'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40003')
	  ],
	  'commandReplyInput' => [
		'topic' => 'worker.command.reply',
		'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40004')
	  ]
	],
  
  ],
  'runner' => [
	'commandInput' => [
	  'topic' => 'example',
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40001')
	],
	'commandReplyOutput' => [
	  'topic' => 'example',
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40002')
	],
  ],
  'logger' => [
	'handlers' => [
	  new StreamHandler(dirname(__DIR__).'/test_worker/managerLog', Logger::DEBUG)
	]
  ]
];