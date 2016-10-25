<?php

namespace SAREhub\Component\Worker\Service;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class ServiceSupport implements Service {
	
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
		try {
			if (!$this->isStarted()) {
				$this->getLogger()->info('service starting ...');
				$this->doStart();
				$this->started = true;
				$this->stopped = false;
				$this->getLogger()->info('service started');
			}
		} catch (\Exception $e) {
			$this->getLogger()->error($e);
		}
	}
	
	/**
	 * Executed on every service tick.
	 * @throws \Exception When something was wrong.
	 */
	public function tick() {
		try {
			if ($this->isRunning()) {
				$this->doTick();
			}
		} catch (\Exception $e) {
			$this->getLogger()->error($e);
			$this->stop();
		}
	}
	
	/**
	 * Executed for stop service
	 * @throws \Exception When something was wrong.
	 */
	public function stop() {
		if ($this->isStarted()) {
			try {
				$this->getLogger()->info('service stopping ...');
				$this->doStop();
			} catch (\Exception $e) {
				$this->getLogger()->error($e);
			}
			
			$this->started = false;
			$this->stopped = true;
			$this->getLogger()->info('service stopped');
		}
	}
	
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
		if ($this->getLogger() === $logger) {
			throw new \LogicException('set same logger instance');
		}
		$this->logger = $logger;
	}
	
	/**
	 * Contains custom worker start logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doStart();
	
	/**
	 * Contains custom worker tick logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doTick();
	
	/**
	 * Contains custom worker stop logic
	 * @throws \Exception When something was wrong.
	 */
	protected abstract function doStop();
}