<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutputTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $senderMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $formatMock;
	
	/** @var ZmqCommandOutput */
	private $commandOutput;
	
	protected function setUp() {
		$this->senderMock = $this->createMock(RequestSender::class);
		$this->formatMock = $this->createMock(CommandFormat::class);
		$this->commandOutput = new ZmqCommandOutput($this->senderMock, $this->formatMock);
	}
	
	public function testSendCommandWhenNonBlockingMode() {
		$command = new BasicCommand('c');
		$commandData = 'c';
		$this->senderMock->expects($this->once())->method('sendRequest')
		  ->with($commandData, false);
		$this->formatMock->expects($this->once())->method('marshal')
		  ->with($this->identicalTo($command))->willReturn($commandData);
		
		$this->commandOutput->sendCommand($command);
	}
	
	public function testSendWhenBlockingMode() {
		$command = new BasicCommand('c');
		$commandData = 'c';
		$this->senderMock->expects($this->once())->method('sendRequest')
		  ->with($commandData, true);
		$this->formatMock->expects($this->once())->method('marshal')
		  ->with($this->identicalTo($command))->willReturn($commandData);
		
		$this->commandOutput->blockingMode()->sendCommand($command);
	}
	
	public function testGetCommandReplyWhenNonBlockingMode() {
		$this->senderMock->expects($this->once())->method('receiveReply')
		  ->with(false)->willReturn('reply');
		$this->assertEquals('reply', $this->commandOutput->getCommandReply());
	}
	
	public function testGetCommandReplyWhenBlockingMode() {
		$this->senderMock->expects($this->once())->method('receiveReply')
		  ->with(true)->willReturn('reply');
		$this->assertEquals('reply', $this->commandOutput->blockingMode()->getCommandReply());
	}
}
