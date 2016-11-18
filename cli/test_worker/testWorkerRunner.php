<?php

date_default_timezone_set('Europe/Warsaw');

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\ZmqCommandInputServiceFactory;
use SAREhub\Component\Worker\WorkerContext;
use SAREhub\Component\Worker\ZmqWorkerRunnerBootstrap;

require dirname(dirname(__DIR__)).'/vendor/autoload.php';

class TestWorker extends BasicWorker {
	
	protected function doStart() {
		$this->logInfo('doStart');
	}
	
	protected function doTick() {
		$this->logInfo('doTick');
		sleep(1); // hard work simulation
	}
	
	protected function doStop() {
		$this->logInfo('doStop');
	}
	
	protected function doCommand(Command $command, callable $replyCallback) {
		$this->logInfo('doCommand: '.$command);
	}
	
	private function logInfo($message) {
		$this->getLogger()->info(sprintf($message, $this->getId()));
	}
}

$loggerFactory = function ($name) {
	return new Logger($name, [new StreamHandler(__DIR__.'/log')]);
};

/** @var Logger $mainLogger */
$mainLogger = $loggerFactory('main');
$workerContext = WorkerContext::newInstance()
  ->withId($argv[1])
  ->withRootPath(__DIR__);

try {
	ZmqWorkerRunnerBootstrap::runInLoop(
	  ZmqWorkerRunnerBootstrap::newInstance()
		->withWorker(new TestWorker($workerContext))
		->withCommandInputServiceFactory(ZmqCommandInputServiceFactory::newInstance()
		  ->withZmqContext(new ZMQContext())
		  ->withEndpointPrefix('/tmp/zmq_module/test')
		)
		->withLoggerFactory($loggerFactory)
		->create()
	);
} catch (\Exception $e) {
	$mainLogger->critical('Exception outside of runner in test manager worker', [
	  'exception' => $e,
	  'workerId' => $workerContext->getId()
	]);
}