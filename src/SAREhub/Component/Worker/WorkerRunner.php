<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\Standard\StopWorkerCommand;
use SAREhub\Component\Worker\Command\StandardWorkerCommands;
use SAREhub\Component\Worker\Command\WorkerCommand;

class WorkerRunner {
	
	/** @var Worker */
	protected $worker;
	
	/** @var CommandInput */
	protected $commandInput;
	
	/** @var bool */
	protected $running = false;
	
	public function __construct(Worker $worker, CommandInput $commandInput) {
		$this->worker = $worker;
		$this->commandInput = $commandInput;
	}
	
	public function start() {
		if (!$this->isRunning()) {
			$this->worker->onStart();
			$this->running = true;
		}
	}
	
	public function tick() {
		if ($this->isRunning()) {
			if ($command = $this->commandInput->getNextCommand()) {
				$this->processCommand($command);
				if (!$this->isRunning()) {
					return;
				}
			}
			
			$this->worker->onTick();
		}
	}
	
	protected function processCommand(WorkerCommand $command) {
		if ($command instanceof StopWorkerCommand) {
			$this->stop();
			$this->commandInput->sendCommandReply('1');
		}
	}
	
	public function stop() {
		$this->worker->onStop();
		$this->running = false;
	}
	
	public function isRunning() {
		return $this->running;
	}
}