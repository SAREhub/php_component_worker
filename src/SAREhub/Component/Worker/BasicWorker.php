<?php

namespace SAREhub\Component\Worker;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SAREhub\Component\Worker\Command\Command;

/**
 * Worker implementation with auto handle lifecycle and logging support.
 * Use that class for create concrete worker with logic.
 */
abstract class BasicWorker implements Worker, LoggerAwareInterface {
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * @var WorkerContext
	 */
	private $context;
	
	/**
	 * @var bool
	 */
	private $started = false;
	
	public function __construct(WorkerContext $context) {
		$this->context = $context;
	}
	
	public function start() {
		if ($this->isStopped()) {
			$this->doStart();
			$this->started = true;
		}
	}
	
	/**
	 * Contains custom worker start logic
	 * @throws WorkerException When something was wrong.
	 */
	protected abstract function doStart();
	
	public function tick() {
		if ($this->isStarted()) {
			$this->doTick();
		}
	}
	
	/**
	 * Contains custom worker tick logic
	 * @throws WorkerException When something was wrong.
	 */
	protected abstract function doTick();
	
	public function stop() {
		if ($this->isStarted()) {
			$this->doStop();
			$this->started = false;
		}
	}
	
	/**
	 * Contains custom worker stop logic
	 * @throws WorkerException When something was wrong.
	 */
	protected abstract function doStop();
	
	public function processCommand(Command $command) {
		$this->doCommand($command);
	}
	
	/**
	 * Contains custom worker command processing logic.
	 *
	 * @param Command $command
	 * @throws WorkerException When something was wrong.
	 */
	protected abstract function doCommand(Command $command);
	
	public function isStopped() {
		return !$this->isStarted();
	}
	
	public function isStarted() {
		return $this->started;
	}
	
	public function getUuid() {
		return $this->getContext()->getUuid();
	}
	
	public function getContext() {
		return $this->context;
	}
	
	/**
	 * Gets logger assigned to that object.
	 * @return LoggerInterface
	 */
	protected function getLogger() {
		if ($this->logger) {
			$this->logger = new NullLogger();
		}
		
		return $this->logger;
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	
}