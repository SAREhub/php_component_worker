<?php

use PHPUnit\Framework\TestCase;
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
	
	public function testStop() {
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
	
	public function testTickWhenProcessCommandEmptyReply() {
		$command = new BasicCommand('1', 'c');
		$this->commandInputMock->expects($this->once())->method('getNext')->willReturn($command);
		$this->workerMock->method('processCommand')->willReturn(null);
		$this->commandReplyOutputMock->expects($this->once())->method('send')
		  ->with($this->callback(function (CommandReply $reply) {
			  return $reply->getMessage() === 'exception when execute command';
		  }));
		$this->workerRunner->tick();
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
