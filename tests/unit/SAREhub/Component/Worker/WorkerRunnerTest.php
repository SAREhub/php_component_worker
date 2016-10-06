<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\WorkerRunner;

class WorkerRunnerTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $workerMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandInputMock;
	
	/** @var WorkerRunner */
	private $workerRunner;
	
	protected function setUp() {
		$this->workerMock = $this->createMock(SAREhub\Component\Worker\Worker::class);
		$this->commandInputMock = $this->createMock(CommandInput::class);
		$this->workerRunner = WorkerRunner::newWithWorkerAndCommandInput($this->workerMock, $this->commandInputMock);
	}
	
	public function testStart() {
		$this->workerMock->expects($this->once())->method('start');
		$this->workerRunner->start();
	}
	
	public function testTick() {
		$this->workerMock->method('isStarted')->willReturn('true');
		$this->workerMock->expects($this->once())->method('tick');
		$this->workerRunner->tick();
	}
	
	public function testStop() {
		$this->workerMock->expects($this->once())->method('stop');
		$this->workerRunner->stop();
	}
	
	public function testCheckCommandWhenCommandWasReceive() {
		$commmand = new BasicCommand('test');
		$commandReply = 'reply';
		$this->commandInputMock->expects($this->once())->method('getNextCommand')->willReturn($commmand);
		$this->commandInputMock->expects($this->once())->method('sendCommandReply')->with($commandReply);
		$this->workerMock->expects($this->once())->method('processCommand')
		  ->with($this->identicalTo($commmand))->willReturn($commandReply);
		
		$this->workerRunner->tick();
	}
	
	public function testCheckCommandWhenNoCommand() {
		$this->commandInputMock->expects($this->once())->method('getNextCommand')->willReturn(null);
		$this->workerMock->expects($this->never())->method('processCommand');
		$this->workerRunner->tick();
	}
	
	public function testCheckCommandWhenWorkerIsStopped() {
		$this->commandInputMock->expects($this->once())->method('getNextCommand')->willReturn(new BasicCommand('name'));
		$this->commandInputMock->expects($this->once())->method('sendCommandReply');
		$this->workerMock->expects($this->once())->method('processCommand');
		
		$this->workerRunner->tick();
	}
}
