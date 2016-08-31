<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\CommandOutput;
use Symfony\Component\Process\Process;

/**
 * Represents worker process. Allows sending commands to worker via WorkerCommandOutput
 */
class WorkerProcess {
	
	/** @var WorkerInfo */
	protected $workerInfo;
	
	/** @var CommandOutput */
	protected $workerCommandOutput;
	
	/** @var Process */
	protected $process;
	
	public function __construct(WorkerInfo $workerInfo, CommandOutput $workerCommandOutput, Process $process) {
		$this->workerInfo = $workerInfo;
		$this->workerCommandOutput = $workerCommandOutput;
		$this->process = $process;
	}
	
	/**
	 * Starts worker process.
	 */
	public function start() {
		$this->process->start();
	}
	
	/**
	 * Kills process via signal
	 */
	public function kill() {
		$this->process->stop();
	}
	
	/**
	 * @return WorkerInfo
	 */
	public function getWorkerInfo() {
		return $this->workerInfo;
	}
	
	/**
	 * @return CommandOutput
	 */
	public function getCommandOutput() {
		return $this->workerCommandOutput;
	}
	
	/**
	 * @return Process
	 */
	public function getProcess() {
		return $this->process;
	}
	
	
}