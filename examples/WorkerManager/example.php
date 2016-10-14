<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SAREhub\Commons\Misc\Dsn;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\Command\JsonCommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandOutput;
use SAREhub\Component\Worker\Command\ZmqCommandReplyInput;
use SAREhub\Component\Worker\Manager\ManagerCommands;
use SAREhub\Component\Worker\Manager\WorkerCommandService;
use SAREhub\Component\Worker\Manager\WorkerManager;
use SAREhub\Component\Worker\Manager\WorkerProcessFactory;
use SAREhub\Component\Worker\Manager\WorkerProcessService;
use SAREhub\Component\Worker\WorkerContext;

$context = WorkerContext::newInstance()->withId('manager');

$workerProcessService = WorkerProcessService::newInstance()
  ->withWorkerProcessFactory(WorkerProcessFactory::newInstance()
	->withRunnerScriptPath(__DIR__.'/workerScript.php'));


$zmqContext = new ZMQContext();
$workerCommandService = WorkerCommandService::newInstance()
  ->withCommandOutput(ZmqCommandOutput::newInstance()
	->withPublisher(Publisher::inContext($zmqContext)
	  ->bind(Dsn::tcp()->endpoint('127.0.0.1:30002'))
	)
	->withCommandFormat(JsonCommandFormat::newInstance()))
  ->withCommandReplyInput(ZmqCommandReplyInput::newInstance()
	->withSubscriber(Subscriber::inContext($zmqContext)
	  ->subscribe('worker.command.reply')
	  ->connect(Dsn::tcp()->endpoint('127.0.0.1:30001'))
	)
  );

$workerManager = WorkerManager::newInstanceWithContext($context)
  ->withProcessService($workerProcessService)
  ->withCommandService($workerCommandService);

$logger = new Logger('manager');
$logger->pushHandler(new StreamHandler(__DIR__.'/log', Logger::DEBUG));
$workerManager->setLogger($logger);

$workerManager->start();

$replyCallback = function ($command, $reply) use ($workerManager) {
	$workerManager->getLogger()->info('replyCallback: ', [
	  'command' => $command,
	  'reply' => $reply
	]);
};

$workerManager->processCommand(ManagerCommands::start('1', 'worker1'), $replyCallback);
$workerManager->processCommand(ManagerCommands::start('2', 'worker2'), $replyCallback);
$workerManager->processCommand(ManagerCommands::start('3', 'worker3'), $replyCallback);

sleep(20);

$workerManager->stop();


