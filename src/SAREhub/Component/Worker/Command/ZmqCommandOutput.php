<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutput implements CommandOutput {
	
	protected $commandSender;
	protected $blockingMode = false;
	protected $serializer;
	
	protected function __construct(RequestSender $commandSender) {
		$this->commandSender = $commandSender;
	}
	
	/**
	 * @param RequestSender $commandSender
	 * @return ZmqCommandOutput
	 */
	public static function forSender(RequestSender $commandSender) {
		return new self($commandSender);
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
		$this->blockingMode = true;
		return $this;
	}
	
	/**
	 * @param callable $serializer
	 * @return $this
	 */
	public function serializer(callable $serializer) {
		$this->serializer = $serializer;
		return $this;
	}
	
	public function sendCommand(Command $command) {
		$serializer = $this->serializer;
		$this->commandSender->sendRequest($serializer($command), $this->isInBlockingMode());
		return $this;
	}
	
	public function getCommandReply() {
		return $this->commandSender->receiveReply($this->isInBlockingMode());
	}
	
	public function isInBlockingMode() {
		return $this->blockingMode;
	}
}