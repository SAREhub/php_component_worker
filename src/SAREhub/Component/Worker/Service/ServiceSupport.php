<?php

namespace SAREhub\Component\Worker\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class ServiceSupport implements Service, LoggerAwareInterface {
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	private $started = false;
	private $stopped = true;
	
	/**
	 * Executed for start service.
	 * @throws \Exception When something was wrong.
	 */
	public function start() {
		if (!$this->isStarted()) {
			$this->doStart();
			$this->started = true;
			$this->stopped = false;
		}
	}
	
	/**
	 * Contains custom worker start logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doStart();
	
	/**
	 * Executed on every service tick.
	 * @throws \Exception When something was wrong.
	 */
	public function tick() {
		if ($this->isRunning()) {
			$this->doTick();
		}
	}
	
	/**
	 * Contains custom worker tick logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doTick();
	
	/**
	 * Executed for stop service
	 * @throws \Exception When something was wrong.
	 */
	public function stop() {
		if ($this->isStarted()) {
			$this->doStop();
			$this->started = false;
			$this->stopped = true;
		}
	}
	
	/**
	 * Contains custom worker stop logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doStop();
	
	/**
	 * @return boolean
	 */
	public function isStarted() {
		return $this->started;
	}
	
	/**
	 * @return boolean
	 */
	public function isStopped() {
		return $this->stopped;
	}
	
	/**
	 * @return boolean
	 */
	public function isRunning() {
		return $this->started;
	}
	
	/**
	 * Gets logger assigned to that object.
	 * @return LoggerInterface
	 */
	public function getLogger() {
		if ($this->logger === null) {
			$this->logger = new NullLogger();
		}
		
		return $this->logger;
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}