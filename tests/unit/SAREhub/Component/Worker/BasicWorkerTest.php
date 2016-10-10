<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\WorkerContext;

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
	 * @var TestWorker
	 */
	private $worker;
	
	/**
	 * @var PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
	 */
	private $methodSpy;
	
	protected function setUp() {
		parent::setUp();
		$this->worker = new TestWorker(WorkerContext::newInstance());
	}
	
	public function testStartWhenStoppedThenDoStart() {
		$this->createWorkerMethodSpy('doStart');
		$this->worker->start();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
	}
	
	public function testIsStartedWhenStoppedThenReturnFalse() {
		$this->assertFalse($this->worker->isStarted());
	}
	
	public function testIsStartedWhenStartedThenReturnTrue() {
		$this->worker->start();
		$this->assertTrue($this->worker->isStarted());
	}
	
	public function testStartWhenStartedThenNoop() {
		$this->createWorkerMethodSpy('doStart');
		$this->worker->start();
		$this->worker->start();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
	}
	
	public function testStopWhenStartedThenDoStop() {
		$this->createWorkerMethodSpy('doStop');
		$this->worker->start();
		$this->worker->stop();
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
	}
	
	public function testStopWhenStartedThenIsStartedReturnFalse() {
		$this->worker->start();
		$this->worker->stop();
		$this->assertFalse($this->worker->isStarted());
	}
	
	public function testStopWhenStopped() {
		$this->createWorkerMethodSpy('doStop');
		$this->worker->stop();
		$this->assertFalse($this->methodSpy->hasBeenInvoked());
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
	
	public function testProcessCommandThenDoCommand() {
		$this->createWorkerMethodSpy('doCommand');
		$command = new BasicCommand('test');
		$this->worker->processCommand($command);
		$this->assertEquals(1, $this->methodSpy->getInvocationCount());
		$this->assertSame($command, $this->methodSpy->getInvocations()[0]->parameters[0]);
	}
	
	public function testProcessCommandThenReturnReply() {
		$this->createWorkerSpy('doCommand');
		$expectedReply = CommandReply::success('r');
		$this->worker->expects($this->once())->method('doCommand')->willReturn($expectedReply);
		$this->assertSame($expectedReply, $this->worker->processCommand(new BasicCommand('test')));
	}
	
	private function createWorkerMethodSpy($method) {
		$this->createWorkerSpy($method);
		$this->worker->expects($this->methodSpy = $this->any())->method($method);
	}
	
	private function createWorkerSpy($method) {
		$this->worker = $this->createPartialMock(TestWorker::class, [$method]);
	}
}
