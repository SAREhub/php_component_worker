<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;

class ZmqCommandReplyInput implements CommandReplyInput {
	
	/**
	 * @var Subscriber
	 */
	private $subscriber;
	
	protected function __construct() {
		
	}
	
	/**
	 * @return ZmqCommandReplyInput
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Subscriber $subscriber
	 * @return $this
	 */
	public function withSubscriber(Subscriber $subscriber) {
		$this->subscriber = $subscriber;
		return $this;
	}
	
	public function getNext($wait = false) {
		$replyData = $this->getSubscriber()->receive($wait);
		return ($replyData) ? CommandReply::createFromJson($replyData['body']) : null;
	}
	
	public function close() {
		$this->getSubscriber()->close();
	}
	
	public function getSubscriber() {
		return $this->subscriber;
	}
}