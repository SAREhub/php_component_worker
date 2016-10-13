<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\ZmqCommandReplyOutput;

class ZmqCommandReplyOutputTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $publisher;
	
	/**
	 * @var ZmqCommandReplyOutput
	 */
	private $output;
	
	private $topic = 'reply';
	
	protected function setUp() {
		parent::setUp();
		$this->publisher = $this->createMock(Publisher::class);
		$this->output = new ZmqCommandReplyOutput($this->publisher);
	}
	
	public function testSendThenPublisherPublish() {
		$reply = CommandReply::success("id", "reply");
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $reply->toJson(), false);
		$this->output->send($this->topic, $reply);
	}
	
	public function testSendWhenWaitThenPublisherPublishWithWait() {
		$reply = CommandReply::success("id", "reply");
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $reply->toJson(), true);
		$this->output->send($this->topic, $reply, true);
	}
	
	public function testClose() {
		$this->publisher->expects($this->once())->method('unbind');
		$this->output->close();
	}
}
