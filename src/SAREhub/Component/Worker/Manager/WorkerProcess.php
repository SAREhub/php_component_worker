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
	private $uuid;
	
	private $process;
	
	public function __construct($uuid, Process $process) {
		$this->uuid = $uuid;
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
	 * @return string
	 */
	public function getUuid() {
		return $this->uuid;
	}
	
	/**
	 * @return Process
	 */
	public function getProcess() {
		return $this->process;
	}
}