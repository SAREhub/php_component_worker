<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\Command;

class TestWorker extends BasicWorker {
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
		
	}
	
	protected function doStop() {
		
	}
	
	protected function doCommand(Command $command) {
		
	}
}

class BasicWorkerTest extends TestCase {
	
	/**
	 * @var Worker
	 */
	private $worker;
	
	/**
	 * @var PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
	 */
	private $methodSpy;
	
	public function testStartWhenStopped() {
		$this->createWorkerMethodSpy('doStart');
		$this->worker->start();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
		
		$this->assertTrue($this->worker->isStarted());
		$this->assertFalse($this->worker->isStopped());
	}
	
	public function testStartWhenStarted() {
		$this->createWorkerMethodSpy('doStart');
		$this->worker->start();
		$this->worker->start();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
		
		$this->assertTrue($this->worker->isStarted());
		$this->assertFalse($this->worker->isStopped());
	}
	
	public function testStopWhenStarted() {
		$this->createWorkerMethodSpy('doStop');
		$this->worker->start();
		$this->worker->stop();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
		
		$this->assertFalse($this->worker->isStarted());
		$this->assertTrue($this->worker->isStopped());
	}
	
	public function testStopWhenStopped() {
		$this->createWorkerMethodSpy('doStop');
		$this->worker->stop();
		$this->assertFalse($this->methodSpy->hasBeenInvoked());
		
		$this->assertFalse($this->worker->isStarted());
		$this->assertTrue($this->worker->isStopped());
	}
	
	public function testTickWhenStopped() {
		$this->createWorkerMethodSpy('doTick');
		$this->worker->tick();
		$this->assertFalse($this->methodSpy->hasBeenInvoked());
	}
	
	public function testTickWhenStarted() {
		$this->createWorkerMethodSpy('doTick');
		$this->worker->start();
		$this->worker->tick();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
	}
	
	public function testProcessCommand() {
		$this->createWorkerMethodSpy('doCommand');
		$command = new BasicCommand('test');
		$this->worker->processCommand($command);
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
		$this->assertSame($command, $this->methodSpy->getInvocations()[0]->parameters[0]);
	}
	
	private function createWorkerMethodSpy($method) {
		$this->worker = $this->createPartialMock(TestWorker::class, [$method]);
		$this->worker->expects($this->methodSpy = $this->any())->method($method);
	}
}
