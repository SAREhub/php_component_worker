<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Manager\ManagerCommands;
use SAREhub\Component\Worker\Manager\WorkerCommandRequest;
use SAREhub\Component\Worker\Manager\WorkerCommandService;
use SAREhub\Component\Worker\Manager\WorkerManager;
use SAREhub\Component\Worker\Manager\WorkerProcessService;
use SAREhub\Component\Worker\WorkerCommands;
use SAREhub\Component\Worker\WorkerContext;

class WorkerManagerTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $processServiceMock;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $commandServiceMock;
	
	/**
	 * @var WorkerManager
	 */
	private $manager;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $replyCallback;
	
	public function testStartCommandWhenNotExistsThenSuccess() {
		$workerId = 'worker1';
		$command = ManagerCommands::start('1', $workerId);
		$this->assertCommandReply($this->once(), $command, function (CommandReply $reply) {
			return $reply->isSuccess();
		});
		
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	public function testStartCommandWhenNotExistsThenRegisterInProcessService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start('1', $workerId);
		$this->processServiceMock->expects($this->once())->method('registerWorker')->with($workerId);
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	public function testStartCommandWhenExistsThenError() {
		$workerId = 'worker1';
		$command = ManagerCommands::start('1', $workerId);
		$this->processServiceMock->method('hasWorker')->with($workerId)->willReturn(true);
		
		$this->assertCommandReply($this->once(), $command, function (CommandReply $reply) {
			return $reply->isError();
		});
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	public function testStartWorkerCommandWhenExistsThenNotRegisterInProcessService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start('1', $workerId);
		$this->processServiceMock->method('hasWorker')->with($workerId)->willReturn(true);
		$this->processServiceMock->expects($this->never())->method('registerWorker');
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	public function testStopWorkerCommandWhenExistsThenCommandServiceProcess() {
		$command = ManagerCommands::stop('1', 'worker1');
		$this->commandServiceMock->expects($this->once())->method('process')
		  ->with($this->callback(function (WorkerCommandRequest $request) use ($command) {
			  return $request->getWorkerId() === 'worker1' &&
			  $request->getCommand()->getName() === WorkerCommands::STOP &&
			  $request->getCommand()->getCorrelationId() === $command->getCorrelationId();
		  }));
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	public function testStopWorkerCommandWhenReplyThenProcessServiceUnregister() {
		$this->processServiceMock->expects($this->once())->method('unregisterWorker')->with('worker1');
		$command = ManagerCommands::stop('1', 'worker1');
		
		$this->commandServiceMock->expects($this->once())->method('process')
		  ->with($this->callback(function (WorkerCommandRequest $request) {
			  ($request->getReplyCallback())($request, CommandReply::success('1', 'm'));
			  return true;
		  }));
		
		$this->manager->processCommand($command, $this->replyCallback);
	}
	
	protected function setUp() {
		parent::setUp();
		$this->processServiceMock = $this->createMock(WorkerProcessService::class);
		$this->commandServiceMock = $this->createMock(WorkerCommandService::class);
		$this->manager = (new WorkerManager(WorkerContext::newInstance()))
		  ->withProcessService($this->processServiceMock)
		  ->withCommandService($this->commandServiceMock);
		
		$this->replyCallback = $this->createPartialMock(stdClass::class, ['__invoke']);
	}
	
	private function assertCommandReply($invokeTimes, Command $command, callable $callback) {
		$this->replyCallback->expects($invokeTimes)->method('__invoke')
		  ->with($command, $this->callback($callback));
	}
}
