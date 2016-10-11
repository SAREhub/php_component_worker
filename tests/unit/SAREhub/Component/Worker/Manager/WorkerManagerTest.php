<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Manager\ManagerCommands;
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
	
	protected function setUp() {
		parent::setUp();
		$this->processServiceMock = $this->createMock(WorkerProcessService::class);
		$this->commandServiceMock = $this->createMock(WorkerCommandService::class);
		$this->manager = (new WorkerManager(WorkerContext::newInstance()))
		  ->withProcessService($this->processServiceMock)
		  ->withCommandService($this->commandServiceMock);
	}
	
	public function testStartCommandWhenNotExistsThenSuccess() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$reply = $this->manager->processCommand($command);
		$this->assertTrue($reply->isSuccess());
	}
	
	public function testStartCommandWhenNotExistsThenRegisterInProcessService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->processServiceMock->expects($this->once())->method('register')->with($workerId);
		$this->manager->processCommand($command);
	}
	
	public function testStartCommandWhenNotExistsThenStartInProcessService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->processServiceMock->expects($this->once())->method('start')->with($workerId);
		$this->manager->processCommand($command);
	}
	
	public function testStartCommandWhenNotExistsThenRegisterInCommandService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->commandServiceMock->expects($this->once())->method('register')->with($workerId);
		$this->manager->processCommand($command);
	}
	
	public function testStartCommandWhenExistsThenError() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->processServiceMock->method('has')->with($workerId)->willReturn(true);
		$reply = $this->manager->processCommand($command);
		$this->assertTrue($reply->isError());
	}
	
	public function testStartWorkerCommandWhenExistsThenNotRegisterInProcessService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->processServiceMock->method('has')->with($workerId)->willReturn(true);
		$this->processServiceMock->expects($this->never())->method('register');
		$this->manager->processCommand($command);
	}
	
	public function testStartWorkerCommandWhenExistsThenNotRegisterInCommandService() {
		$workerId = 'worker1';
		$command = ManagerCommands::start($workerId);
		$this->processServiceMock->method('has')->with($workerId)->willReturn(true);
		$this->commandServiceMock->expects($this->never())->method('register');
		$this->manager->processCommand($command);
	}
	
	public function testStopWorkerCommandWhenExistsThenSendCommand() {
		$command = ManagerCommands::stop('worker1');
		$this->commandServiceMock->expects($this->once())->method('sendCommand')
		  ->with('worker1', $this->callback(function (Command $command) {
			  return $command->getName() === WorkerCommands::STOP;
		  }))->willReturn(CommandReply::success('reply'));
		$this->manager->processCommand($command);
	}
	
	public function testStopWorkerCommandWhenExistsThenReturnReply() {
		$command = ManagerCommands::stop('worker1');
		$expectedReply = CommandReply::success('reply');
		$this->commandServiceMock->method('sendCommand')->willReturn($expectedReply);
		$this->assertSame($expectedReply, $this->manager->processCommand($command));
	}
}
