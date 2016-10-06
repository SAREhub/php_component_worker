<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\RequestReply\RequestReceiver;

/**
 * Worker command input based on ZMQ Request/Reply method
 * It will be create in non blocking mode
 */
class ZmqCommandInput implements CommandInput {
	
	/**
	 * @var RequestReceiver
	 */
	protected $receiver;
	
	/**
	 * @var bool
	 */
	protected $blockingMode = false;
	
	/**
	 * @var CommandFormat
	 */
	private $format;
	
	/**
	 * @var Command
	 */
	private $lastCommand = null;
	
	public function __construct(RequestReceiver $receiver, CommandFormat $format) {
		$this->receiver = $receiver;
		$this->format = $format;
	}
	
	/**
	 * Sets blocking mode for getNextCommand - will waits for next command
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
	
	public function getNextCommand() {
		if ($this->lastCommand) {
			throw new CommandException(
			  "Can't get next command, when reply for last wasn't sent, last command was: ".$this->lastCommand
			);
		}
		
		$commandData = $this->receiver->receiveRequest($this->isInBlockingMode());
		$this->lastCommand = ($commandData) ? $this->format->unmarshal($commandData) : null;
		return $this->lastCommand;
	}
	
	public function sendCommandReply($reply) {
		if ($this->lastCommand === null) {
			throw new CommandException("Reply can be sent only when receive command");
		}
		$this->receiver->sendReply($reply, $this->isInBlockingMode());
		$this->lastCommand = null;
	}
	
	/**
	 * @return bool
	 */
	public function isInBlockingMode() {
		return $this->blockingMode;
	}
}