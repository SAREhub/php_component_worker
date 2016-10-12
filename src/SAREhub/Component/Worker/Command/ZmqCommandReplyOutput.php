<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;

class ZmqCommandReplyOutput implements CommandReplyOutput {
	
	/**
	 * @var string
	 */
	private $publishTopic = '';
	
	/**
	 * @var Publisher
	 */
	private $publisher;
	
	public function __construct(Publisher $publisher, $publishTopic = '') {
		$this->publisher = $publisher;
		$this->publishTopic = $publishTopic;
	}
	
	public function send(Command $command, CommandReply $reply, $wait = false) {
		$this->getPublisher()->publish($this->getPublishTopic(), $reply->toJson(), $wait);
	}
	
	public function close() {
		$this->publisher->unbind();
	}
	
	public function getPublisher() {
		return $this->publisher;
	}
	
	public function getPublishTopic() {
		return $this->publishTopic;
	}
}