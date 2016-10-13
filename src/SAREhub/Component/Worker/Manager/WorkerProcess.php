<?php

namespace SAREhub\Component\Worker\Manager;

use Symfony\Component\Process\Process;

/**
 * Represents worker process
 */
class WorkerProcess {
	
	/**
	 * @var string
	 */
	private $id;
	
	private $process;
	
	public function __construct($id, Process $process) {
		$this->id = $id;
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
	 * @return bool
	 */
	public function isRunning() {
		return $this->process->isRunning();
	}
	
	/**
	 * @return int|null
	 */
	public function getPid() {
		return $this->process->getPid();
	}
	/**
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return Process
	 */
	public function getProcess() {
		return $this->process;
	}
}