<?php

use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use SAREhub\Commons\Misc\Dsn;

$forwardersConfig = [
  'commandOutput' => [
	'input' => Dsn::tcp()->endpoint('127.0.0.1:40001'),
	'output' => Dsn::tcp()->endpoint('127.0.0.1:40002')
  ],
  'commandReplyInput' => [
	'input' => Dsn::tcp()->endpoint('127.0.0.1:40005'),
	'output' => Dsn::tcp()->endpoint('127.0.0.1:40006')
  ]
];

return [
  'manager' => [
    'configRootPath' => __DIR__.'/managers',
    'commandInputEndpoint' => $forwardersConfig['commandOutput']['output'],
    'commandReplyOutputEndpoint' => $forwardersConfig['commandReplyInput']['input']
  ],
  
  'forwarders' => $forwardersConfig,
  
  'commandService' => [
	'commandOutput' => [
	  'endpoint' => $forwardersConfig['commandOutput']['input']
	],
	'commandReplyInput' => [
	  'topic' => 'worker-cli',
	  'endpoint' => $forwardersConfig['commandReplyInput']['output']
	]
  ],
  'logging' => [
	'handlers' => [
	  new SyslogHandler('sarehub_worker_cli', LOG_USER, Logger::INFO)
	],
	'processors' => [
	  new PsrLogMessageProcessor()
	]
  ]
];