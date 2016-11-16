<?php

use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use SAREhub\Commons\Process\PcntlSignals;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\CommandReplyOutput;
use SAREhub\Component\Worker\Command\ZmqCommandInputServiceFactory;
use SAREhub\Component\Worker\Worker;
use SAREhub\Component\Worker\WorkerRunner;
use SAREhub\Component\Worker\ZmqWorkerRunnerBootstrap;

class ZmqWorkerRunnerBootstrapTest extends TestCase {
	
	private $worker;
	private $commandInputServiceFactory;
	/**
	 * @var ZmqWorkerRunnerBootstrap
	 */
	private $bootstrap;
	
	protected function setUp() {
		$this->worker = $this->createMock(Worker::class);
		$this->commandInputServiceFactory = $this->createMock(ZmqCommandInputServiceFactory::class);
		$this->commandInputServiceFactory->method('withCommandInputTopic')
		  ->willReturn($this->commandInputServiceFactory);
		$this->commandInputServiceFactory->method('withEndpointPrefix')
		  ->willReturn($this->commandInputServiceFactory);
		
		$this->commandInputServiceFactory->method('createCommandInput')
		  ->willReturn($this->createMock(CommandInput::class));
		$this->commandInputServiceFactory->method('createCommandReplyOutput')
		  ->willReturn($this->createMock(CommandReplyOutput::class));
		
		$this->bootstrap = ZmqWorkerRunnerBootstrap::newInstance()
		  ->withWorker($this->worker)
		  ->withEndpointPrefix('/tmp/test')
		  ->withCommandInputServiceFactory($this->commandInputServiceFactory);
	}
	
	public function testCreateThenCreateCommandInput() {
		$this->commandInputServiceFactory->expects($this->once())
		  ->method('createCommandInput')
		  ->willReturn($this->createMock(CommandInput::class));
		$this->bootstrap->create();
	}
	
	public function testCreateThenCreateCommandReplyOutput() {
		
		$this->commandInputServiceFactory->expects($this->once())
		  ->method('createCommandReplyOutput')
		  ->willReturn($this->createMock(CommandReplyOutput::class));
		$this->bootstrap->create();
	}
	
	public function testCreateThenWithCommandInputTopic() {
		$this->worker->method('getId')->willReturn('workerId');
		$this->commandInputServiceFactory->expects($this->once())
		  ->method('withCommandInputTopic')
		  ->with('workerId');
		$this->bootstrap->create();
	}
	
	public function testCreateThenWithEndpointPrefix() {
		$this->commandInputServiceFactory->expects($this->once())
		  ->method('withEndpointPrefix')
		  ->with('/tmp/test');
		$this->bootstrap->create();
	}
	
	public function testCreateThenReturnRunner() {
		$this->assertInstanceOf(WorkerRunner::class, $this->bootstrap->create());
	}
	
	public function testCreateThenRunnerHasWorker() {
		$runner = $this->bootstrap->create();
		$this->assertSame($this->worker, $runner->getWorker());
	}
	
	public function testCreateThenRunnerHasGlobalPcntl() {
		$runner = $this->bootstrap->create();
		$this->assertSame(PcntlSignals::getGlobal(), $runner->getPcntlSignals());
	}
	
	public function testCreateThenRunnerHasCommandInput() {
		$runner = $this->bootstrap->create();
		$expected = $this->commandInputServiceFactory->createCommandInput();
		$this->assertSame($expected, $runner->getCommandInput());
	}
	
	public function testCreateWhenNoLoggerFactoryThenRunnerNullLogger() {
		$this->assertInstanceOf(NullLogger::class, $this->bootstrap->create()->getLogger());
	}
	
	public function testCreateWhenLoggerFactoryThenRunnerLogger() {
		$loggerFactory = $this->createLoggerFactory();
		$logger = $this->createMock(Logger::class);
		$loggerFactory->method('__invoke')->willReturn($logger);
		$this->bootstrap->withLoggerFactory($loggerFactory);
		$this->assertSame($logger, $this->bootstrap->create()->getLogger());
	}
	
	public function testCreateWhenNoLoggerFactoryThenWorkerNeverSetLogger() {
		$this->worker->expects($this->never())->method('setLogger');
		$this->bootstrap->create();
	}
	
	public function testCreateWhenLoggerFactoryThenWorkerSetLogger() {
		$loggerFactory = $this->createLoggerFactory();
		$logger = $this->createMock(Logger::class);
		$loggerFactory->method('__invoke')->willReturn($logger);
		$this->bootstrap->withLoggerFactory($loggerFactory);
		$this->worker->expects($this->once())->method('setLogger')->with($this->identicalTo($logger));
		$this->bootstrap->create();
	}
	
	private function createLoggerFactory() {
		return $this->createPartialMock(stdClass::class, ['__invoke']);
	}
	
	public function testRunLoopThenStart() {
		$runner = $this->createMock(WorkerRunner::class);
		$runner->expects($this->once())->method('start');
		ZmqWorkerRunnerBootstrap::runInLoop($runner);
	}
	
	public function testRunLoopThenStop() {
		$runner = $this->createMock(WorkerRunner::class);
		$runner->expects($this->once())->method('stop');
		ZmqWorkerRunnerBootstrap::runInLoop($runner);
	}
	
	public function testRunLoopWhenRunningThenTick() {
		$runner = $this->createMock(WorkerRunner::class);
		$runner->method('isRunning')->willReturnOnConsecutiveCalls(true, false);
		$runner->expects($this->once())->method('tick');
		ZmqWorkerRunnerBootstrap::runInLoop($runner);
	}
}
