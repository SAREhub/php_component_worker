<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Manager\WorkerProcess;
use SAREhub\Component\Worker\Manager\WorkerProcessFactory;
use SAREhub\Component\Worker\Manager\WorkerProcessService;

class WorkerProcessServiceTest extends TestCase {
	
	/**
	 * @var WorkerProcessService
	 */
	private $service;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $factoryMock;
	
	private $process;
	
	protected function setUp() {
		parent::setUp();
		$this->factoryMock = $this->createMock(WorkerProcessFactory::class);
		$this->process = $this->createMock(WorkerProcess::class);
		$this->factoryMock->method('create')->willReturn($this->process);
		$this->service = WorkerProcessService::newInstance()
		  ->withWorkerProcessFactory($this->factoryMock);
		
	}
	
	public function testRegisterThenHasWorker() {
		$this->service->registerWorker('worker');
		$this->assertTrue($this->service->hasWorker('worker'));
	}
	
	public function testRegisterThenCreateProcess() {
		$this->factoryMock->expects($this->once())->method('create')->willReturn($this->process);
		$this->service->registerWorker('worker');
	}
	
	public function testRegisterThenProcessStart() {
		$this->process->expects($this->once())->method('start');
		$this->service->registerWorker('worker');
	}
	
	public function testRegisterWhenWorkerExistsThenNotCreateProcess() {
		$this->service->registerWorker('worker');
		$this->factoryMock->expects($this->never())->method('create');
		$this->service->registerWorker('worker');
	}
	
	public function testRegisterWhenWorkerExistsThenHasOld() {
		$this->service->registerWorker('worker');
		$this->service->registerWorker('worker');
		$this->assertTrue($this->service->hasWorker('worker'));
	}
	
	public function testUnregisterWhenExists() {
		$this->service->registerWorker('worker');
		$this->service->unregisterWorker('worker');
		$this->assertFalse($this->service->hasWorker('worker'));
	}
	
	public function testKillWhenExists() {
		$this->service->registerWorker('id');
		$this->process->expects($this->once())->method('kill');
		$this->service->killWorker('id');
	}
	
	public function testHasWhenNotExistsThenReturnFalse() {
		$this->assertFalse($this->service->hasWorker('not_exists'));
	}
	
	public function testGetWorkerPidWhenExistsThenReturnPid() {
		$this->service->registerWorker('worker');
		$this->process->method('getPid')->willReturn(1000);
		$this->assertEquals(1000, $this->service->getWorkerPid('worker'));
	}
}
