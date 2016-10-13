<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Component\Worker\Command\BasicCommand;
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
		$this->output = new ZmqCommandReplyOutput($this->publisher, $this->topic);
	}
	
	public function testSendThenPublisherPublish() {
		$reply = CommandReply::success("reply");
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $reply->toJson(), false);
		$this->output->send(new BasicCommand("name"), $reply);
	}
	
	public function testSendWhenWaitThenPublisherPublishWithWait() {
		$reply = CommandReply::success("reply");
		$this->publisher->expects($this->once())->method('publish')
		  ->with($this->topic, $reply->toJson(), true);
		$this->output->send(new BasicCommand("name"), $reply, true);
	}
	
	public function testClose() {
		$this->publisher->expects($this->once())->method('unbind');
		$this->output->close();
	}
}