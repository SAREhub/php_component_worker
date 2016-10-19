<?php

use SAREhub\Commons\Misc\Dsn;

return [
  'manager' => [
	'configRootPath' => __DIR__.'/managers'
  ],
  'commandService' => [
	'commandOutput' => [
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40001')
	],
	'commandReplyInput' => [
	  'topic' => 'worker-cli',
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40002')
	]
  ]
];