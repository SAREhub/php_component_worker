<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Process\PcntlSignals;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandReplyOutput;
use SAREhub\Component\Worker\WorkerRunner;

class WorkerRunnerTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $worker;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandInput;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandReplyOutput;
	
	/** @var WorkerRunner */
	private $workerRunner;
	
	public function testStartThenWorkerStart() {
		$this->worker->expects($this->once())->method('start');
		$this->workerRunner->start();
	}
	
	public function testTick() {
		$this->workerRunner->start();
		$this->worker->expects($this->once())->method('tick');
		$this->workerRunner->tick();
	}
	
	public function testStopWhenStarted() {
		$this->workerRunner->start();
		$this->worker->expects($this->once())->method('stop');
		$this->commandInput->expects($this->once())->method('close');
		$this->commandReplyOutput->expects($this->once())->method('close');
		$this->workerRunner->stop();
	}
	
	public function testTickWhenCommandThenWorkerProcessCommand() {
		$this->workerRunner->start();
		$command = new BasicCommand('1', 'test');
		$this->commandInput->expects($this->once())->method('getNext')->willReturn($command);
		$this->worker->expects($this->once())->method('processCommand')
		  ->with($this->identicalTo($command));
		
		$this->workerRunner->tick();
	}
	
	public function testTickWhenNoCommandThenWorkerNotProcessCommand() {
		$this->workerRunner->start();
		$this->commandInput->expects($this->once())->method('getNext')->willReturn(null);
		$this->worker->expects($this->never())->method('processCommand');
		$this->workerRunner->tick();
	}
	
	public function testTickWhenProcessCommandException() {
		$this->workerRunner->start();
		$command = new BasicCommand('1', 'c');
		$this->commandInput->expects($this->once())->method('getNext')->willReturn($command);
		$this->worker->method('processCommand')->willThrowException(new \Exception('m'));
		$this->commandReplyOutput->expects($this->once())->method('send')
		  ->with($this->callback(function (CommandReply $reply) {
			  return $reply->getMessage() === 'exception when execute command';
		  }));
		$this->workerRunner->tick();
	}
	
	public function testTickThenCheckPendingSignals() {
		$this->workerRunner->start();
		echo extension_loaded('pcntl');
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->once())->method('checkPendingSignals');
		$this->workerRunner->usePcntl($signals);
		$this->workerRunner->tick();
	}
	
	public function testUsePcntlThenHandleSIGINT_AND_SIGTERM() {
		$runner = $this->workerRunner;
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->atLeast(2))->method('handle')
		  ->withConsecutive([
			PcntlSignals::SIGINT, $this->callback(function (array $callback) use ($runner) {
			  return $callback[0] === $runner && $callback[1] === 'stop';
			})], [
			PcntlSignals::SIGTERM, $this->callback(function (array $callback) use ($runner) {
				return $callback[0] === $runner && $callback[1] === 'stop';
			})
		  ]);
		$runner->usePcntl($signals);
	}
	
	public function testUsePcntlWhenInstallTrueThenInstall() {
		$runner = $this->workerRunner;
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->once())->method('install');
		$runner->usePcntl($signals);
	}
	
	public function testUsePcntlWhenInstallFalseThenInstall() {
		$runner = $this->workerRunner;
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->never())->method('install');
		$runner->usePcntl($signals, false);
	}
	
	protected function setUp() {
		$this->worker = $this->createMock(SAREhub\Component\Worker\Worker::class);
		$this->commandInput = $this->createMock(CommandInput::class);
		$this->commandReplyOutput = $this->createMock(CommandReplyOutput::class);
		$this->workerRunner = WorkerRunner::newInstance()
		  ->withWorker($this->worker)
		  ->withCommandInput($this->commandInput)
		  ->withCommandReplyOutput($this->commandReplyOutput);
	}
}
