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
	private $reply;
	
	protected function setUp() {
		parent::setUp();
		$this->publisher = $this->createMock(Publisher::class);
		$this->output = new ZmqCommandReplyOutput($this->publisher, $this->topic);
		$this->reply = CommandReply::success("id", "reply");
	}
	
	public function testSendThenPublisherPublish() {
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $this->reply->toJson(), false);
		$this->output->send($this->reply);
	}
	
	public function testSendWhenWaitThenPublisherPublishWithWait() {
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $this->reply->toJson(), true);
		$this->output->send($this->reply, true);
	}
	
	public function testClose() {
		$this->publisher->expects($this->once())->method('unbind');
		$this->output->close();
	}
}
