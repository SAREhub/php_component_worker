<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

return [
  'id' => '',
  'manager' => [
	'processService' => [
	  'runnerScript' => '',
	  'arguments' => [],
	  'workingDirectory' => ''
	],
	'commandService' => [
	  'commandOutput' => [
		'endpoint' => ''
	  ],
	  'commandReplyInput' => [
		'topic' => 'worker.command.reply',
		'endpoint' => ''
	  ]
	],
  
  ],
  'runner' => [
	'commandInput' => [
	  'topic' => '',
	  'endpoint' => ''
	],
	'commandReplyOutput' => [
	  'topic' => '',
	  'endpoint' => ''
	],
  ],
  'logger' => [
	'handlers' => [
	  [
		new StreamHandler('', Logger::INFO)
	  ]
	]
  ]
];