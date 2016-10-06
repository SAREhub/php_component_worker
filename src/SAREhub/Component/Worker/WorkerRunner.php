<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\CommandInput;

/**
 * Helper class for run worker.
 */
class WorkerRunner {
	
	/**
	 * @var Worker
	 * */
	private $worker;
	
	/**
	 * @var CommandInput
	 */
	private $commandInput;
	
	protected function __construct(Worker $worker, CommandInput $commandInput) {
		$this->worker = $worker;
		$this->commandInput = $commandInput;
	}
	
	public static function newWithWorkerAndCommandInput(Worker $worker, CommandInput $commandInput) {
		return new self($worker, $commandInput);
	}
	
	public function start() {
		$this->getWorker()->start();
	}
	
	public function tick() {
		$this->checkCommand();
		if ($this->getWorker()->isStarted()) {
			$this->getWorker()->tick();
			return true;
		}
		
		return false;
	}
	
	public function stop() {
		$this->getWorker()->stop();
	}
	
	protected function checkCommand() {
		if ($command = $this->getCommandInput()->getNextCommand()) {
			$reply = $this->getWorker()->processCommand($command);
			$this->getCommandInput()->sendCommandReply($reply);
		}
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