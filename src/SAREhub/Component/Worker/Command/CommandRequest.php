<?php

namespace SAREhub\Component\Worker\Command;

class CommandRequest {
	
	const DEFAULT_REPLY_TIMEOUT = 30;
	
	/**
	 * @var string
	 */
	private $topic;
	
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
	
	private $processAsync = true;
	
	protected function __construct() {
		
	}
	
	/**
	 * @return CommandRequest
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param $topic
	 * @return $this
	 */
	public function withTopic($topic) {
		$this->topic = $topic;
		return $this;
	}
	
	/**
	 * @param Command $command
	 * @return $this
	 */
	public function withCommand(Command $command) {
		$this->command = $command;
		return $this;
	}
	
	/**
	 * @param $timeout
	 * @return $this
	 */
	public function withReplyTimeout($timeout) {
		$this->replyTimeout = $timeout;
		return $this;
	}
	
	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function withReplyCallback(callable $callback) {
		$this->replyCallback = $callback;
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function syncMode() {
		$this->processAsync = false;
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
	
	public function getTopic() {
		return $this->topic;
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
	
	/**
	 * @return bool
	 */
	public function isAsync() {
		return $this->processAsync;
	}
}