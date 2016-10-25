<?php

namespace SAREhub\Component\Worker\Manager;

use SAREhub\Component\Worker\Service\ServiceSupport;

class WorkerProcessService extends ServiceSupport {
	
	/**
	 * @var WorkerProcessFactory
	 */
	private $processFactory;
	
	/**
	 * @var WorkerProcess[]
	 */
	private $processList = [];
	
	protected function __construct() {
	}
	
	/**
	 * @return WorkerProcessService
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param WorkerProcessFactory $factory
	 * @return $this
	 */
	public function withWorkerProcessFactory(WorkerProcessFactory $factory) {
		$this->processFactory = $factory;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getWorkerList() {
		return array_keys($this->processList);
	}
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
	}
	
	
	protected function doStop() {
		
	}
	/**
	 * @param string $workerId
	 * @return WorkerProcess
	 */
	public function registerWorker($workerId) {
		if (!$this->hasWorker($workerId)) {
			$this->getLogger()->info("$workerId registering");
			$process = $this->processFactory->create($workerId);
			$process->start();
			$this->processList[$workerId] = $process;
			$this->getLogger()->info("$workerId registered with PID: ".$process->getPid());
		}
		
		$this->getLogger()->info("$workerId registered before");
	}
	
	/**
	 * @param string $workerId
	 */
	public function unregisterWorker($workerId) {
		if ($this->hasWorker($workerId)) {
			unset($this->processList[$workerId]);
			$this->getLogger()->info("$workerId unregistered");
		}
	}
	
	public function killWorker($workerId) {
		if ($process = $this->get($workerId)) {
			$process->kill();
			$this->getLogger()->info("worker process: $workerId killed");
		}
	}
	
	public function getWorkerPid($workerId) {
		if ($process = $this->get($workerId)) {
			return $process->getPid();
		}
		
		return 0;
	}
	/**
	 * @param string $workerId
	 * @return null|WorkerProcess
	 */
	protected function get($workerId) {
		return $this->hasWorker($workerId) ? $this->processList[$workerId] : null;
	}
	
	/**
	 * @param $workerId
	 * @return boolean
	 */
	public function isWorkerRunning($workerId) {
		if ($process = $this->get($workerId)) {
			return $process->isRunning();
		}
		
		return false;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function hasWorker($id) {
		return isset($this->processList[$id]);
	}
	
	
}