<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\ZmqCommandReplyInput;

class ZmqCommandReplyInputTest extends TestCase {
	
	private $subscriber;
	
	private $input;
	
	protected function setUp() {
		parent::setUp();
		$this->subscriber = $this->createMock(Subscriber::class);
		$this->input = new ZmqCommandReplyInput($this->subscriber);
	}
	
	public function testGetNextThenSubscriberCallReceive() {
		$this->subscriber->expects($this->once())->method('receive')->with(false);
		$this->input->getNext();
	}
	
	public function testGetNextWhenWaitThenSubscriberCallReceiveWithWait() {
		$this->subscriber->expects($this->once())->method('receive')->with(true);
		$this->input->getNext(true);
	}
	
	public function testGetNextWhenNotMessageThenReturnNull() {
		$this->subscriber->method('receive')->willReturn(null);
		$this->assertNull($this->input->getNext());
	}
	
	public function testGetNextWhenMessageThenReturnReplyInstance() {
		$this->subscriber->method('receive')->willReturn([
		  'topic' => 'topic1',
		  'body' => CommandReply::success('1', 'test')->toJson()
		]);
		$this->assertInstanceOf(CommandReply::class, $this->input->getNext());
	}
	
	public function testCloseThenSubscriberDisconnect() {
		$this->subscriber->expects($this->once())->method('disconnect');
		$this->input->close();
	}
	
}