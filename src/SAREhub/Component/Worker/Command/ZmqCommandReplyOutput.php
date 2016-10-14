<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;

class ZmqCommandReplyOutput implements CommandReplyOutput {
	
	/**
	 * @var Publisher
	 */
	private $publisher;
	
	/**
	 * @var string
	 */
	private $publishTopic;
	
	protected function __construct() {
	}
	
	/**
	 * @return ZmqCommandReplyOutput
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Publisher $publisher
	 * @return $this
	 */
	public function withPublisher(Publisher $publisher) {
		$this->publisher = $publisher;
		return $this;
	}
	
	/**
	 * @param $publishTopic
	 * @return $this
	 */
	public function withPublishTopic($publishTopic) {
		$this->publishTopic = $publishTopic;
		return $this;
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