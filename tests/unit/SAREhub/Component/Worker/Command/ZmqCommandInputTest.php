<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Commons\Zmq\RequestReply\RequestReceiver;

class ZmqCommandInputTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $receiverMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $deserializerMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandMock;
	
	/** @var ZmqCommandInput */
	private $commandInput;
	
	protected function setUp() {
		$this->receiverMock = $this->createMock(RequestReceiver::class);
		$this->deserializerMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
		$this->commandMock = $this->createMock(Command::class);
		$this->deserializerMock->method('__invoke')->willReturn($this->commandMock);
		
		$this->commandInput = ZmqCommandInput::forReceiver($this->receiverMock)
		  ->deserializer($this->deserializerMock);
	}
	
	public function testGetNextCommand() {
		$this->receiverMock->expects($this->once())
		  ->method('receiveRequest')
		  ->with(false)
		  ->willReturn("command");
		$this->deserializerMock->expects($this->once())
		  ->method('__invoke')
		  ->with('command')
		  ->willReturn($this->commandMock);
		$this->assertSame($this->commandMock, $this->commandInput->getNextCommand());
	}
	
	public function testGetNextCommandBlocking() {
		$this->receiverMock->expects($this->once())
		  ->method('receiveRequest')
		  ->with(true)
		  ->willReturn("command");
		$this->deserializerMock->expects($this->once())
		  ->method('__invoke')
		  ->with('command')
		  ->willReturn($this->commandMock);
		$this->assertSame($this->commandMock, $this->commandInput->blockingMode()->getNextCommand());
	}
	
	public function testGetNextCommandWhenNotSent() {
		$this->receiverMock->expects($this->once())->method('receiveRequest')->with(false)->willReturn(false);
		$this->assertNull($this->commandInput->getNextCommand());
	}
	
	public function testSendReply() {
		$this->receiverMock->expects($this->once())
		  ->method('sendReply')
		  ->with('reply', false)
		  ->willReturn($this->receiverMock);
		$this->commandInput->sendCommandReply('reply');
	}
	
	public function testSendReplyBlocking() {
		$this->receiverMock->expects($this->once())
		  ->method('sendReply')
		  ->with('reply', true)
		  ->willReturn($this->receiverMock);
		$this->commandInput->blockingMode()->sendCommandReply('reply');
	}
}
