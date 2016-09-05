<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutputTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $senderMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $serializerMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandMock;
	
	/** @var ZmqCommandOutput */
	private $commandOutput;
	
	protected function setUp() {
		$this->senderMock = $this->createMock(RequestSender::class);
		$this->serializerMock = $this->getMockBuilder(\stdClass::class)
		  ->setMethods(['__invoke'])
		  ->getMock();
		$this->commandMock = $this->createMock(Command::class);
		
		
		$this->commandOutput = ZmqCommandOutput::forSender($this->senderMock)
		  ->serializer($this->serializerMock);
	}
	
	public function testSendCommand() {
		$this->senderMock->expects($this->once())
		  ->method('sendRequest')
		  ->with('command', false);
		
		$this->serializerMock->expects($this->once())
		  ->method('__invoke')
		  ->with($this->commandMock)
		  ->willReturn("command");
		$this->assertSame($this->commandOutput, $this->commandOutput->sendCommand($this->commandMock));
	}
	
	public function testSendBlocking() {
		$this->senderMock->expects($this->once())
		  ->method('sendRequest')
		  ->with('command', true);
		
		$this->serializerMock->expects($this->once())
		  ->method('__invoke')
		  ->with($this->commandMock)
		  ->willReturn("command");
		$this->assertSame($this->commandOutput, $this->commandOutput->blockingMode()
		  ->sendCommand($this->commandMock));
	}
	
	public function testGetCommandReply() {
		$this->senderMock->expects($this->once())
		  ->method('receiveReply')
		  ->with(false)
		  ->willReturn('reply');
		$this->assertEquals('reply', $this->commandOutput->getCommandReply());
	}
	
	public function testGetCommandReplyBlocking() {
		$this->senderMock->expects($this->once())
		  ->method('receiveReply')
		  ->with(true)
		  ->willReturn('reply');
		$this->assertEquals('reply', $this->commandOutput->blockingMode()->getCommandReply());
	}
}
