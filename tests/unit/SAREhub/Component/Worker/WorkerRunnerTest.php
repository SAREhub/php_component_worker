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
	private $workerMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandInputMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandReplyOutputMock;
	
	/** @var WorkerRunner */
	private $workerRunner;
	
	public function testStart() {
		$this->workerMock->expects($this->once())->method('start');
		$this->workerRunner->start();
	}
	
	public function testTick() {
		$this->workerMock->expects($this->once())->method('tick');
		$this->workerRunner->tick();
	}
	
	public function testStopWhenStarted() {
		$this->workerRunner->start();
		$this->workerMock->expects($this->once())->method('stop');
		$this->commandInputMock->expects($this->once())->method('close');
		$this->commandReplyOutputMock->expects($this->once())->method('close');
		$this->workerRunner->stop();
	}
	
	public function testTickWhenCommandThenWorkerProcessCommand() {
		$command = new BasicCommand('1', 'test');
		$this->commandInputMock->expects($this->once())->method('getNext')->willReturn($command);
		$this->workerMock->expects($this->once())->method('processCommand')
		  ->with($this->identicalTo($command));
		
		$this->workerRunner->tick();
	}
	
	public function testTickWhenNoCommandThenWorkerNotProcessCommand() {
		$this->commandInputMock->expects($this->once())->method('getNext')->willReturn(null);
		$this->workerMock->expects($this->never())->method('processCommand');
		$this->workerRunner->tick();
	}
	
	public function testTickWhenProcessCommandException() {
		$command = new BasicCommand('1', 'c');
		$this->commandInputMock->expects($this->once())->method('getNext')->willReturn($command);
		$this->workerMock->method('processCommand')->willThrowException(new \Exception('m'));
		$this->commandReplyOutputMock->expects($this->once())->method('send')
		  ->with($this->callback(function (CommandReply $reply) {
			  return $reply->getMessage() === 'exception when execute command';
		  }));
		$this->workerRunner->tick();
	}
	
	public function testTickThenCheckPendingSignals() {
		echo extension_loaded('pcntl');
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->once())->method('checkPendingSignals');
		$this->workerRunner->usePcntl($signals);
		$this->workerRunner->tick();
		var_dump($signals === $this->workerRunner->getPcntlSignals());
	}
	
	public function testUsePcntlThenHandleSIGINT() {
		$runner = $this->workerRunner;
		$signals = $this->createMock(PcntlSignals::class);
		$signals->expects($this->once())->method('handle')
		  ->with(PcntlSignals::SIGINT, $this->callback(function (array $callback) use ($runner) {
			  return $callback[0] === $runner && $callback[1] === 'stop';
		  }));
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
		$this->workerMock = $this->createMock(SAREhub\Component\Worker\Worker::class);
		$this->commandInputMock = $this->createMock(CommandInput::class);
		$this->commandReplyOutputMock = $this->createMock(CommandReplyOutput::class);
		$this->workerRunner = WorkerRunner::newInstance()
		  ->withWorker($this->workerMock)
		  ->withCommandInput($this->commandInputMock)
		  ->withCommandReplyOutput($this->commandReplyOutputMock);
	}
}
