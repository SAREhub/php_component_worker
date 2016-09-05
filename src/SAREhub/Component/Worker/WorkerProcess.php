<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\CommandOutput;
use Symfony\Component\Process\Process;

/**
 * Represents worker process. Allows sending commands to worker via WorkerCommandOutput
 */
class WorkerProcess {
	
	protected $workerInfo;
	protected $commandOutput = null;
	protected $process;
	
	protected function __construct(WorkerInfo $workerInfo, Process $process) {
		$this->workerInfo = $workerInfo;
		$this->process = $process;
	}
	
	/**
	 * @param WorkerInfo $workerInfo
	 * @param Process $process
	 * @return WorkerProcess
	 */
	public static function getFor(WorkerInfo $workerInfo, Process $process) {
		return new self($workerInfo, $process);
	}
	
	/**
	 * @param CommandOutput $commandOutput
	 * @return $this
	 */
	public function commandOutput(CommandOutput $commandOutput) {
		$this->commandOutput = $commandOutput;
		return $this;
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
	 * @return string
	 */
	public function getWorkerUuid() {
		return $this->workerInfo->uuid;
	}
	
	/**
	 * @return CommandOutput
	 */
	public function getCommandOutput() {
		return $this->commandOutput;
	}
	
	/**
	 * @return Process
	 */
	public function getProcess() {
		return $this->process;
	}
}