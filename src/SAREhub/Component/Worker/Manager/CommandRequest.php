<?php

namespace SAREhub\Component\Worker\Manager;


use SAREhub\Component\Worker\Command\Command;

class CommandRequest {
	
	const DEFAULT_REPLY_TIMEOUT = 30;
	
	/**
	 * @var Command
	 */
	private $command;
	
	/**
	 * @var int
	 */
	private $replyTimeout = self::DEFAULT_REPLY_TIMEOUT;
	
	/**
	 * @var int
	 */
	private $sentTime = 0;
	
	/**
	 * @var callable
	 */
	private $replyCallback;
	
	protected function __construct() {
		
	}
	
	public static function newInstance() {
		return new self();
	}
	
	public function withCommand(Command $command) {
		$this->command = $command;
		return $this;
	}
	
	public function withReplyTimeout($timeout) {
		$this->replyTimeout = $timeout;
		return $this;
	}
	
	public function withReplyCallback(callable $callback) {
		$this->replyCallback = $callback;
		return $this;
	}
	
	public function markAsSent($now) {
		$this->sentTime = $now;
	}
	
	/**
	 * @return bool
	 */
	public function isSent() {
		return $this->getSentTime() > 0;
	}
	
	/**
	 * @param $now
	 * @return bool
	 */
	public function isReplyTimeout($now) {
		return $this->isSent() && $now >= ($this->getSentTime() + $this->getReplyTimeout());
	}
	
	/**
	 * @return Command
	 */
	public function getCommand() {
		return $this->command;
	}
	
	/**
	 * @return int
	 */
	public function getSentTime() {
		return $this->sentTime;
	}
	
	/**
	 * @return int
	 */
	public function getReplyTimeout() {
		return $this->replyTimeout;
	}
	
	/**
	 * @return callable
	 */
	public function getReplyCallback() {
		return $this->replyCallback;
	}
}