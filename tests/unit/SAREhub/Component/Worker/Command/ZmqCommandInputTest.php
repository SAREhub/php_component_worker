<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandInput;

class ZmqCommandInputTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $subscriberMock;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $commandFormatMock;
	
	/**
	 * @var ZmqCommandInput
	 */
	private $commandInput;
	
	private $commandData = ['topic' => 'command', 'body' => 'data'];
	
	protected function setUp() {
		$this->subscriberMock = $this->createMock(Subscriber::class);
		$this->commandFormatMock = $this->createMock(CommandFormat::class);
		$this->commandInput = ZmqCommandInput::newInstance()
		  ->withCommandSubscriber($this->subscriberMock)
		  ->withCommandFormat($this->commandFormatMock);
	}
	
	public function testGetNextThenSubscriberReceive() {
		$this->subscriberMock->expects($this->once())->method('receive')->with(false);
		$this->commandInput->getNext();
	}
	
	public function testGetNextWhenCommandThenCommandFormatUnmarshal() {
		$this->subscriberMock->method('receive')->willReturn($this->commandData);
		$this->commandFormatMock->expects($this->once())->method('unmarshal')
		  ->with($this->commandData['body']);
		$this->commandInput->getNext();
	}
	
	public function testGetNextWhenCommandThenReturnCommand() {
		$command = new BasicCommand('1', "test");
		$this->subscriberMock->method('receive')->willReturn($this->commandData);
		$this->commandFormatMock->method('unmarshal')->willReturn($command);
		$this->assertSame($command, $this->commandInput->getNext());
	}
	
	public function testGetNextWhenWaitThenReceiveInWait() {
		$this->subscriberMock->method('receive')->with(true);
		$this->commandInput->getNext(true);
	}
	
	public function testGetNextWhenNotSent() {
		$this->subscriberMock->method('receive')->willReturn(false);
		$this->commandFormatMock->expects($this->never())->method('unmarshal');
		$this->assertNull($this->commandInput->getNext());
	}
	
	public function testClose() {
		$this->subscriberMock->expects($this->once())->method('disconnect');
		$this->commandInput->close();
	}
}
