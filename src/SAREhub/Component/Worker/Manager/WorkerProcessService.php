<?php

namespace SAREhub\Component\Worker\Manager;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class WorkerProcessService implements LoggerAwareInterface {
	
	private $logger;
	
	/**
	 * @var WorkerProcessFactory
	 */
	private $processFactory;
	
	/**
	 * @var WorkerProcess[]
	 */
	private $processList = [];
	
	public function __construct(WorkerProcessFactory $factory) {
		$this->processFactory = $factory;
		$this->logger = new NullLogger();
	}
	
	/**
	 * @param string $uuid
	 * @return WorkerProcess
	 */
	public function register($uuid) {
		if (!$this->has($uuid)) {
			$process = $this->processFactory->create($uuid);
			$this->processList[$uuid] = $process;
			return $process;
		}
	}
	
	/**
	 * @param string $uuid
	 */
	public function unregister($uuid) {
		if ($this->has($uuid)) {
			unset($this->processList['uuid']);
		}
	}
	
	public function start($uuid) {
		if ($process = $this->get($uuid)) {
			$process->start();
		}
	}
	
	public function kill($uuid) {
		if ($process = $this->get($uuid)) {
			$process->kill();
		}
	}
	
	/**
	 * @param string $uuid
	 * @return null|WorkerProcess
	 */
	protected function get($uuid) {
		return $this->has($uuid) ? $this->processList[$uuid] : null;
	}
	
	/**
	 * @param string $uuid
	 * @return bool
	 */
	public function has($uuid) {
		return isset($this->processList[$uuid]);
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}