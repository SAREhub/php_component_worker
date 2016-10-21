<?php

use SAREhub\Commons\Misc\Dsn;

return [
  'manager' => [
    'configRootPath' => __DIR__.'/managers',
    'forwarders' => [
	  'commandOutput' => [
	    'input' => Dsn::tcp()->endpoint('127.0.0.1:40001'),
	    'output' => Dsn::tcp()->endpoint('127.0.0.1:40002')
	  ],
	  'commandReplyInput' => [
	    'input' => Dsn::tcp()->endpoint('127.0.0.1:40005'),
	    'output' => Dsn::tcp()->endpoint('127.0.0.1:40006')
	  ]
    ],
  ],
  'commandService' => [
	'commandOutput' => [
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40001')
	],
	'commandReplyInput' => [
	  'topic' => 'worker-cli',
	  'endpoint' => Dsn::tcp()->endpoint('127.0.0.1:40006')
	]
  ]
];