<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Manager\WorkerProcess;
use Symfony\Component\Process\Process;

class WorkerProcessTest extends TestCase {
	
	private $uuid = 'id';
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $processMock;
	
	/**
	 * @var WorkerProcess
	 */
	private $workerProcess;
	
	protected function setUp() {
		$this->processMock = $this->createMock(Process::class);
		$this->workerProcess = new WorkerProcess($this->uuid, $this->processMock);
	}
	
	public function testCreate() {
		$this->assertEquals($this->uuid, $this->workerProcess->getUuid());
		$this->assertSame($this->processMock, $this->workerProcess->getProcess());
	}
	
	public function testStart() {
		$this->processMock->expects($this->once())->method('start');
		$this->workerProcess->start();
	}
	
	
	public function testKill() {
		$this->processMock->expects($this->once())->method('stop');
		$this->workerProcess->kill();
	}
	
	public function testIsRunning() {
		$this->processMock->expects($this->once())->method('isRunning')->willReturn(true);
		$this->assertTrue($this->workerProcess->isRunning());
	}
}
