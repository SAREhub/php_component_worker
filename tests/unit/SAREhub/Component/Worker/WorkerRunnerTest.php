<?php

namespace SAREhub\Component\Worker;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\Standard\StopWorkerCommand;
use SAREhub\Component\Worker\Command\StandardWorkerCommands;

class WorkerRunnerTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $workerMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandInputMock;
	
	/** @var WorkerRunner */
	private $workerRunner;
	
	protected function setUp() {
		$this->workerMock = $this->getMockBuilder(Worker::class)->getMock();
		$this->commandInputMock = $this->getMockBuilder(CommandInput::class)->getMock();
		$this->workerRunner = new WorkerRunner($this->workerMock, $this->commandInputMock);
	}
	
	public function testStart() {
		$this->workerMock->expects($this->once())->method('onStart');
		$this->workerRunner->start();
		$this->workerRunner->start();
		$this->assertTrue($this->workerRunner->isRunning());
	}
	
	public function testTick() {
		$this->workerMock->expects($this->once())->method('onTick');
		$this->workerRunner->start();
		$this->commandInputMock->expects($this->once())->method('getNextCommand')->willReturn(null);
		$this->workerRunner->tick();
	}
	
	public function testStop() {
		$this->workerMock->expects($this->once())->method('onStop');
		$this->workerRunner->start();
		$this->workerRunner->stop();
		$this->assertFalse($this->workerRunner->isRunning());
	}
	
	public function testProcessStopCommand() {
		$this->workerMock->expects($this->once())->method('onStop');
		$stopWorkerCommand = $this->getMockBuilder(StopWorkerCommand::class)->disableOriginalConstructor()->getMock();
		$this->commandInputMock->expects($this->once())->method('getNextCommand')->willReturn($stopWorkerCommand);
		
		$this->workerRunner->start();
		$this->workerRunner->tick();
		$this->assertFalse($this->workerRunner->isRunning());
	}
	
}
