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
		$this->service = new WorkerProcessService($this->factoryMock);
		
	}
	
	public function testRegister() {
		$this->service->register('uuid');
		$this->assertTrue($this->service->has('uuid'));
	}
	
	public function testRegisterWhenExists() {
		$this->service->register('uuid');
		$this->factoryMock->expects($this->never())->method('create');
		$this->assertTrue($this->service->has('uuid'));
	}
	
	public function testUnregisterWhenExists() {
		$this->service->register('uuid');
		$this->service->unregister('uuid');
		$this->assertFalse($this->service->has('uuid'));
	}
	
	public function testStartWhenExists() {
		$this->service->register('uuid');
		$this->process->expects($this->once())->method('start');
		$this->service->start('uuid');
	}
	
	public function testKillWhenExists() {
		$this->service->register('uuid');
		$this->process->expects($this->once())->method('kill');
		$this->service->kill('uuid');
	}
	
	public function testHasWhenNotExists() {
		$this->assertFalse($this->service->has('not_exists'));
	}
}
