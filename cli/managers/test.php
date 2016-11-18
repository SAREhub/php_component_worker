<?php

use Monolog\Handler\StreamHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use SAREhub\Component\Worker\Cli\CliWorkerManagerConfigBuilder;
use SAREhub\Component\Worker\Manager\ManagerCommands;
use SAREhub\Component\Worker\WorkerRunner;

return CliWorkerManagerConfigBuilder::newInstance()
  ->withId('test')
  ->withScriptPath(dirname(__DIR__).'/test_worker/testWorkerRunner.php')
  ->withManagerCommandServiceZmqRootPath('tmp/zmq_module')
  ->withOnStart(function (WorkerRunner $runner) {
	  $startCommands = [
		ManagerCommands::start('1', '1'),
		ManagerCommands::start('2', '2'),
		ManagerCommands::start('3', '3')
	  ];
	  foreach ($startCommands as $command) {
		  $runner->processCommand($command, function () {
			  
		  });
	  }
  })
  ->withLoggingConfigFactory(function () {
	  return [
		'handlers' => [
		  new StreamHandler(dirname(__DIR__).'/test_worker/managerLog')
		],
		'processors' => [
		  new PsrLogMessageProcessor()
		]
	  ];
  })
  ->create();