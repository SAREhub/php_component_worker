<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;

class ZmqCommandReplyInput implements CommandReplyInput {
	
	private $subscriber;
	
	/**
	 * @param Subscriber $subscriber
	 */
	public function __construct(Subscriber $subscriber) {
		$this->subscriber = $subscriber;
	}
	
	/**
	 * @param bool $wait
	 * @return null|CommandReply
	 */
	public function getNext($wait = false) {
		$replyData = $this->subscriber->receive($wait);
		return ($replyData) ? CommandReply::createFromJson($replyData['body']) : null;
	}
	
	public function close() {
		$this->subscriber->disconnect();
	}
}