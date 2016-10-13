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
	
	public function __construct(Publisher $publisher, CommandFormat $format) {
		$this->publisher = $publisher;
		$this->format = $format;
	}
	
	public function send($topic, Command $command, $wait = false) {
		$commandData = $this->format->marshal($command);
		$this->getPublisher()->publish($topic, $commandData, $wait);
	}
	
	public function close() {
		$this->getPublisher()->unbind();
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