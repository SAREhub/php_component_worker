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
	 * @param string $id
	 * @return WorkerProcess
	 */
	public function register($id) {
		if (!$this->has($id)) {
			$process = $this->processFactory->create($id);
			$this->processList[$id] = $process;
			$this->getLogger()->info('registered worker process: '.$id);
			return $process;
		}
	}
	
	/**
	 * @param string $id
	 */
	public function unregister($id) {
		if ($this->has($id)) {
			unset($this->processList[$id]);
			$this->getLogger()->info('unregistered worker process: '.$id);
		}
	}
	
	public function start($id) {
		if ($process = $this->get($id)) {
			$process->start();
			$this->getLogger()->info('started worker process: '.$id);
		}
	}
	
	public function kill($id) {
		if ($process = $this->get($id)) {
			$process->kill();
			$this->getLogger()->info('killed worker process: '.$id);
		}
	}
	
	/**
	 * @param string $id
	 * @return null|WorkerProcess
	 */
	protected function get($id) {
		return $this->has($id) ? $this->processList[$id] : null;
	}
	
	/**
	 * @param $id
	 * @return boolean
	 */
	public function isWorkerRunning($id) {
		return $this->get($id)->isRunning();
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function has($id) {
		return isset($this->processList[$id]);
	}
	
	/**
	 * @return LoggerInterface
	 */
	public function getLogger() {
		return $this->logger;
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}