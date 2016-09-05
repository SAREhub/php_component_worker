<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\RequestReply\RequestReceiver;

/**
 * Worker command input based on ZMQ Request/Reply method
 */
class ZmqCommandInput implements CommandInput {
	
	/** @var RequestReceiver */
	protected $commandReceiver;
	
	/** @var bool */
	protected $blockingMode = false;
	
	/** @var callable */
	protected $deserializer;
	
	protected function __construct(RequestReceiver $commandReceiver) {
		$this->commandReceiver = $commandReceiver;
	}
	
	/**
	 * @param RequestReceiver $commandReceiver
	 * @return ZmqCommandInput
	 */
	public static function forReceiver(RequestReceiver $commandReceiver) {
		return new self($commandReceiver);
	}
	
	/**
	 * @return $this
	 */
	public function blockingMode() {
		$this->blockingMode = true;
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function nonBlockingMode() {
		$this->blockingMode = false;
		return $this;
	}
	
	/**
	 * @param callable $deserializer
	 * @return $this
	 */
	public function deserializer(callable $deserializer) {
		$this->deserializer = $deserializer;
		return $this;
	}
	
	public function getNextCommand() {
		if ($commandData = $this->commandReceiver->receiveRequest($this->isInBlockingMode())) {
			$deserializer = $this->deserializer;
			$command = $deserializer($commandData);
			if ($command instanceof Command) {
				return $command;
			}
			
			throw new CommandException("Deserializer must return Command instance: ".$command);
		}
		
		return null;
	}
	
	public function sendCommandReply($reply) {
		$this->commandReceiver->sendReply($reply, $this->isInBlockingMode());
		return $this;
	}
	
	/**
	 * @return bool
	 */
	public function isInBlockingMode() {
		return $this->blockingMode;
	}
}