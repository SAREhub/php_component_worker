<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;

/**
 * Worker command input based on ZMQ Request/Reply method
 * It will be create in non blocking mode
 */
class ZmqCommandInput implements CommandInput {
	
	/**
	 * @var Subscriber
	 */
	private $commandSubscriber;
	
	/**
	 * @var CommandFormat
	 */
	private $format;
	
	
	public function __construct(Subscriber $commandSubscriber, CommandFormat $format) {
		$this->commandSubscriber = $commandSubscriber;
		$this->format = $format;
	}
	
	public function getNext($wait = false) {
		$commandData = $this->getCommandSubscriber()->receive($wait);
		return ($commandData) ? $this->format->unmarshal($commandData['body']) : null;
	}
	
	public function close() {
		$this->getCommandSubscriber()->disconnect();
	}
	
	/**
	 * @return Subscriber
	 */
	public function getCommandSubscriber() {
		return $this->commandSubscriber;
	}
}