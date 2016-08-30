<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\WorkerCommand;
use SAREhub\Component\Worker\Command\WorkerCommandOutput;
use Symfony\Component\Process\Process;

/**
 * Represents worker process. Allows sending commands to worker via WorkerCommandOutput
 */
class WorkerProcess {
	
	/** @var WorkerInfo */
	protected $workerInfo;
	
	/** @var WorkerCommandOutput */
	protected $workerCommandOutput;
	
	/** @var Process */
	protected $process;
	
	public function __construct(WorkerInfo $workerInfo, WorkerCommandOutput $workerCommandOutput, Process $process) {
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
	 * @param WorkerCommand $command
	 * @return string
	 */
	public function sendCommand(WorkerCommand $command) {
		$this->workerCommandOutput->sendCommand($command);
	}
	
	/**
	 * @return string
	 */
	public function getLastCommandConfirmation() {
		return $this->workerCommandOutput->getCommandConfirmation();
	}
	
	/**
	 * Killing process via signal
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
	 * @return Process
	 */
	public function getProcess() {
		return $this->process;
	}
	
	
}