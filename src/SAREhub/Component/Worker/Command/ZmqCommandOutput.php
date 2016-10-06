<?php

namespace SAREhub\Component\Worker\Command;


use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutput implements CommandOutput {
	
	private $sender;
	private $blockingMode = false;
	private $format;
	
	public function __construct(RequestSender $sender, CommandFormat $format) {
		$this->sender = $sender;
		$this->format = $format;
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
	
	public function sendCommand(Command $command) {
		$commandData = $this->format->marshal($command);
		$this->sender->sendRequest($commandData, $this->isInBlockingMode());
	}
	
	public function getCommandReply() {
		return $this->sender->receiveReply($this->isInBlockingMode());
	}
	
	public function isInBlockingMode() {
		return $this->blockingMode;
	}
}