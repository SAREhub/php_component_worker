<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;

class TestBasicWorker extends BasicWorker {
	
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
	 * @var TestBasicWorker
	 */
	private $worker;
	
	protected function setUp() {
		parent::setUp();
		$this->worker = $this->createPartialMock(TestBasicWorker::class, ['doCommand']);
	}
	
	public function testProcessCommandThenDoCommand() {
		$spy = $this->getMethodSpy('doCommand');
		$this->worker->processCommand(new BasicCommand('test'));
		$this->assertEquals(1, $spy->getInvocationCount());
	}
	
	public function testProcessCommandThenDoCommandWithCommandParameter() {
		$spy = $this->getMethodSpy('doCommand');
		$command = new BasicCommand('test');
		$this->worker->processCommand($command);
		$this->assertSame($command, $spy->getInvocations()[0]->parameters[0]);
	}
	
	public function testProcessCommandThenReturnReply() {
		$expectedReply = CommandReply::success('r');
		$this->worker->expects($this->once())->method('doCommand')->willReturn($expectedReply);
		$this->assertSame($expectedReply, $this->worker->processCommand(new BasicCommand('test')));
	}
	
	private function getMethodSpy($method) {
		$this->worker->expects($methodSpy = $this->any())->method($method);
		return $methodSpy;
	}
}
