<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Commons\Zmq\RequestReply\RequestReceiver;

class ZmqCommandInputTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $receiverMock;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $commandFormatMock;
	
	/**
	 * @var ZmqCommandInput
	 */
	private $commandInput;
	
	private $commandData = 'command_data';
	
	protected function setUp() {
		$this->receiverMock = $this->createMock(RequestReceiver::class);
		$this->commandFormatMock = $this->createMock(CommandFormat::class);
		$this->commandInput = new ZmqCommandInput($this->receiverMock, $this->commandFormatMock);
	}
	
	public function testGetNextCommandWhenNonBlockingMode() {
		$command = new BasicCommand('name');
		$this->receiverMock->method('receiveRequest')
		  ->with(false)->willReturn($this->commandData);
		$this->commandFormatMock->expects($this->once())->method('unmarshal')
		  ->with($this->commandData)->willReturn($command);
		$this->assertSame($command, $this->commandInput->getNextCommand());
	}
	
	public function testGetNextCommandWhenBlockingMode() {
		$command = new BasicCommand('name');
		$this->receiverMock->method('receiveRequest')->with(true)->willReturn($this->commandData);
		$this->commandFormatMock->expects($this->once())->method('unmarshal')
		  ->with($this->commandData)->willReturn($command);
		$this->assertSame($command, $this->commandInput->blockingMode()->getNextCommand());
	}
	
	public function testGetNextCommandWhenNotSent() {
		$this->receiverMock->expects($this->once())->method('receiveRequest')->willReturn(false);
		$this->commandFormatMock->expects($this->never())->method('unmarshal');
		$this->assertNull($this->commandInput->getNextCommand());
	}
	
	public function testSendReplyWhenCommandWasReceive() {
		$this->receiverMock->method('receiveRequest')->willReturn('c');
		$this->commandFormatMock->method('unmarshal')->willReturn(new BasicCommand('c'));
		$this->commandInput->getNextCommand();
		
		$this->receiverMock->expects($this->once())->method('sendReply')->with('reply', false);
		$this->commandInput->sendCommandReply('reply');
	}
	
	public function testSendReplyBlocking() {
		$this->receiverMock->method('receiveRequest')->willReturn('c');
		$this->commandFormatMock->method('unmarshal')->willReturn(new BasicCommand('c'));
		$this->commandInput->getNextCommand();
		
		$this->receiverMock->expects($this->once())->method('sendReply')->with('reply', true);
		$this->commandInput->blockingMode()->sendCommandReply('reply');
	}
}
