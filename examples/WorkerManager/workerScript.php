<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SAREhub\Commons\Misc\Dsn;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\JsonCommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandInput;
use SAREhub\Component\Worker\Command\ZmqCommandReplyOutput;
use SAREhub\Component\Worker\WorkerContext;
use SAREhub\Component\Worker\WorkerRunner;

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

$context = WorkerContext::newInstance()
  ->withId($argv[1])
  ->withRootPath(__DIR__);

$logger = new Logger($context->getId());
$logger->pushHandler(new StreamHandler(__DIR__.'/log', Logger::DEBUG));

try {
$zmqContext = new ZMQContext();
$runner = WorkerRunner::newInstance()
  ->withWorker(new TestWorker($context))
  ->withCommandInput(ZmqCommandInput::newInstance()
	->withCommandSubscriber(Subscriber::inContext($zmqContext)
	  ->subscribe($context->getId())
	  ->connect(Dsn::tcp()->endpoint('127.0.0.1:30001'))
	)
	->withCommandFormat(JsonCommandFormat::newInstance()))
  ->withCommandReplyOutput(ZmqCommandReplyOutput::newInstance()
	->withPublisher(Publisher::inContext($zmqContext)
	  ->connect(Dsn::tcp()->endpoint('127.0.0.1:30002')))
	->withPublishTopic('worker.command.reply')
  );
	
	$logger->info("init");
$runner->getWorker()->setLogger($logger);
$runner->setLogger($logger);
$runner->start();
while ($runner->isRunning()) {
	$runner->tick();
}
$runner->stop();
	
} catch (Exception $e) {
	$logger->error($e);
}