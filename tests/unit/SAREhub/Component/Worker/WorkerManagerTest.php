<?php

namespace SAREhub\Component\Worker;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\Standard\StopWorkerCommand;

class WorkerManagerTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $workerProcessFactoryMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $workerProcessMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $workerCommandOutputMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandInputMock;
	
	/**
	 * @var WorkerManager
	 */
	private $workerManager;
	
	private $workerUuid = 'worker';
	
	protected function setUp() {
		$this->workerProcessMock = $this->createMock(WorkerProcess::class);
		$this->workerProcessMock->method('getWorkerUuid')->willReturn($this->workerUuid);
		$this->workerCommandOutputMock = $this->createMock(CommandOutput::class);
		$this->workerProcessMock->method('getCommandOutput')->willReturn($this->workerCommandOutputMock);
		
		$this->workerProcessFactoryMock = $this->createMock(WorkerProcessFactory::class);
		$this->workerProcessFactoryMock->method('create')->willReturn($this->workerProcessMock);
		$this->commandInputMock = $this->createMock(CommandInput::class);
		$this->workerManager = WorkerManager::getNewWithUuid('manager')
		  ->workerProcessFactory($this->workerProcessFactoryMock);
	}
	
	public function testStartWorker() {
		$this->workerProcessFactoryMock->expects($this->once())
		  ->method('create')->with($this->isInstanceOf(WorkerInfo::class))
		  ->willReturn($this->workerProcessMock);
		$this->workerProcessMock->expects($this->once())->method('start');
		$this->workerManager->startWorker($this->workerUuid);
		$this->assertEquals([
		  $this->workerUuid => $this->workerProcessMock
		], $this->workerManager->getWorkerProcessList());
	}
	
	public function testStopWorker() {
		$this->workerManager->startWorker($this->workerUuid);
		$this->workerCommandOutputMock->expects($this->once())
		  ->method('sendCommand')
		  ->with(new StopWorkerCommand($this->workerUuid));
		
		$this->assertFalse($this->workerManager->isWaitingForCommandReplyFromWorker());
		$this->workerManager->stopWorker($this->workerUuid);
		$this->assertTrue($this->workerManager->isWaitingForCommandReplyFromWorker());
	}
}
