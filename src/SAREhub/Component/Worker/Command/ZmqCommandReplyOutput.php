<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;

class ZmqCommandReplyOutput implements CommandReplyOutput {
	
	/**
	 * @var Publisher
	 */
	private $publisher;
	
	public function __construct(Publisher $publisher) {
		$this->publisher = $publisher;
	}
	
	public function send($topic, CommandReply $reply, $wait = false) {
		$this->getPublisher()->publish($topic, $reply->toJson(), $wait);
	}
	
	public function close() {
		$this->publisher->unbind();
	}
	
	public function getPublisher() {
		return $this->publisher;
	}
}