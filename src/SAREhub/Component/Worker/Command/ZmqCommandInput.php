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
	private $subscriber;
	
	/**
	 * @var CommandFormat
	 */
	private $format;
	
	protected function __construct() {
	}
	
	/**
	 * @return ZmqCommandInput
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Subscriber $commandSubscriber
	 * @return $this
	 */
	public function withCommandSubscriber(Subscriber $commandSubscriber) {
		$this->subscriber = $commandSubscriber;
		return $this;
	}
	
	/**
	 * @param CommandFormat $format
	 * @return $this
	 */
	public function withCommandFormat(CommandFormat $format) {
		$this->format = $format;
		return $this;
	}
	
	public function getNext($wait = false) {
		$commandData = $this->getSubscriber()->receive($wait);
		return ($commandData) ? $this->format->unmarshal($commandData['body']) : null;
	}
	
	public function close() {
		$this->getSubscriber()->close();
	}
	
	/**
	 * @return Subscriber
	 */
	public function getSubscriber() {
		return $this->subscriber;
	}
	
	/**
	 * @return CommandFormat
	 */
	public function getCommandFormat() {
		return $this->format;
	}
}