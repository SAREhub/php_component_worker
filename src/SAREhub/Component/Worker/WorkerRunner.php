<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\Standard\StopWorkerCommand;
use SAREhub\Component\Worker\Command\StandardWorkerCommands;

/**
 * Helper class for run worker.
 */
class WorkerRunner {
	
	/** @var Worker */
	protected $worker;
	
	/** @var CommandInput */
	protected $commandInput;
	
	/** @var bool */
	protected $running = false;
	
	public function __construct(Worker $worker) {
		$this->worker = $worker;
	}
	
	/**
	 * @param Worker $worker
	 * @return WorkerRunner
	 */
	public static function wrap(Worker $worker) {
		return new self($worker);
	}
	
	/**
	 * @param CommandInput $commandInput
	 * @return $this
	 */
	public function commandInput(CommandInput $commandInput) {
		$this->commandInput = $commandInput;
		return $this;
	}
	
	/**
	 * Starts worker loop.
	 */
	public function run() {
		$this->start();
		while ($this->isRunning()) {
			$this->tick();
		}
	}
	
	/**
	 * Calls once on worker start.
	 */
	public function start() {
		if (!$this->isRunning()) {
			$this->worker->onStart();
			$this->running = true;
		}
	}
	
	/**
	 * Calls per main loop tick.
	 */
	public function tick() {
		$this->checkCommand();
		if ($this->isRunning()) {
			$this->worker->onTick();
		}
	}
	
	protected function checkCommand() {
		if ($command = $this->commandInput->getNextCommand()) {
			if ($command instanceof StopWorkerCommand) {
				$this->stop();
				$this->commandInput->sendCommandReply('1');
			}
		}
	}
	
	/**
	 * Stop worker process
	 */
	public function stop() {
		$this->worker->onStop();
		$this->running = false;
	}
	
	/**
	 * @return bool
	 */
	public function isRunning() {
		return $this->running;
	}
	
	/**
	 * @return Worker
	 */
	public function getWorker() {
		return $this->worker;
	}
	
	/**
	 * @return CommandInput
	 */
	public function getCommandInput() {
		return $this->commandInput;
	}
}