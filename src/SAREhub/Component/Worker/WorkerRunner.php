<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\StandardWorkerCommands;
use SAREhub\Component\Worker\Command\WorkerCommand;
use SAREhub\Component\Worker\Command\WorkerCommandInput;

class WorkerRunner {
	
	/** @var Worker */
	protected $worker;
	
	/** @var WorkerCommandInput */
	protected $commandInput;
	
	/** @var bool */
	protected $running = false;
	
	public function __construct(Worker $worker, WorkerCommandInput $commandInput) {
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
		switch ($command->getName()) {
			case StandardWorkerCommands::STOP_COMMAND_NAME:
				$this->stop();
				break;
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