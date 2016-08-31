<?php

namespace SAREhub\Component\Worker;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Component\Worker\Command\CommandOutput;
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
		$this->commandOutputMock = $this->getMockBuilder(CommandOutput::class)->getMock();
		$this->workerProcess = new WorkerProcess(new WorkerInfo(), $this->commandOutputMock, $this->processMock);
	}
	
	public function testStart() {
		$this->processMock->expects($this->once())->method('start');
		$this->workerProcess->start();
	}
	
	
	public function testKill() {
		$this->processMock->expects($this->once())->method('stop');
		$this->workerProcess->kill();
	}
}
