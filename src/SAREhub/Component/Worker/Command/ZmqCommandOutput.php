<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;

class ZmqCommandOutput implements CommandOutput {
	
	/**
	 * @var Publisher
	 */
	private $publisher;
	
	/**
	 * @var CommandFormat
	 */
	private $format;
	
	protected function __construct() {
	}
	
	/**
	 * @return ZmqCommandOutput
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Publisher $publisher
	 * @return $this
	 */
	public function withPublisher(Publisher $publisher) {
		$this->publisher = $publisher;
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
	
	public function send($topic, Command $command, $wait = false) {
		$commandData = $this->format->marshal($command);
		$this->getPublisher()->publish($topic, $commandData, $wait);
	}
	
	public function close() {
		$this->publisher->close();
	}
	
	/**
	 * @return Publisher
	 */
	public function getPublisher() {
		return $this->publisher;
	}
	
	/**
	 * @return CommandFormat
	 */
	public function getCommandFormat() {
		return $this->format;
	}
}