<?php

namespace SAREhub\Component\Worker;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Component\Worker\Command\WorkerCommand;
use SAREhub\Component\Worker\Command\WorkerCommandOutput;
use Symfony\Component\Process\Process;

class WorkerProcessTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $processMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandOutputMock;
	
	/** @var WorkerProcess */
	private $workerProcess;
	
	protected function setUp() {
		$this->processMock = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
		$this->commandOutputMock = $this->getMockBuilder(WorkerCommandOutput::class)->getMock();
		$this->workerProcess = new WorkerProcess(new WorkerInfo(), $this->commandOutputMock, $this->processMock);
	}
	
	public function testStart() {
		$this->processMock->expects($this->once())->method('start');
		$this->workerProcess->start();
	}
	
	public function testSendCommand() {
		$commandMock = $this->getMockBuilder(WorkerCommand::class)->disableOriginalConstructor()->getMock();
		$this->commandOutputMock->expects($this->once())
		  ->method('sendCommand')
		  ->with($this->identicalTo($commandMock));
		$this->workerProcess->sendCommand($commandMock);
	}
	
	public function testGetLastCommandConfirmation() {
		$this->commandOutputMock->expects($this->once())
		  ->method('getCommandConfirmation')->willReturn('1');
		
		$this->assertEquals('1', $this->workerProcess->getLastCommandConfirmation());
	}
	
	
	public function testKill() {
		$this->processMock->expects($this->once())->method('stop');
		$this->workerProcess->kill();
	}
}
