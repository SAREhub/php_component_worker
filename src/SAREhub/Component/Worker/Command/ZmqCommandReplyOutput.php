<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;

class ZmqCommandReplyOutput implements CommandReplyOutput {
	
	/**
	 * @var Publisher
	 */
	private $publisher;
	
	private $publishTopic;
	
	public function __construct(Publisher $publisher, $publishTopic) {
		$this->publisher = $publisher;
		$this->publishTopic = $publishTopic;
	}
	
	public function send(CommandReply $reply, $wait = false) {
		$this->getPublisher()->publish($this->getPublishTopic(), $reply->toJson(), $wait);
	}
	
	public function close() {
		$this->publisher->unbind();
	}
	
	/**
	 * @return Publisher
	 */
	public function getPublisher() {
		return $this->publisher;
	}
	
	/**
	 * @return string
	 */
	public function getPublishTopic() {
		return $this->publishTopic;
	}
}