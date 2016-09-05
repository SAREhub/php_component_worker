<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\Standard\StartWorkerCommand;
use SAREhub\Component\Worker\Command\Standard\StopWorkerCommand;
use SAREhub\Component\Worker\Command\WorkerCommand;

/**
 * Represents manager for workers.
 * It can start new workers, send commands to worker and kill selected worker when it needs.
 */
class WorkerManager implements Worker {
	
	protected $uuid;
	
	/** @var CommandInput */
	protected $commandInput;
	
	/** @var WorkerProcessFactory */
	protected $workerProcessFactory;
	
	/**
	 * @var WorkerProcess[]
	 */
	protected $workerProcessList = [];
	/**
	 * @var WorkerProcess
	 */
	protected $waitingCommandReplyProcess = null;
	
	protected function __construct($uuid) {
		$this->uuid = $uuid;
	}
	
	/**
	 * @param string $uuid
	 * @return WorkerManager
	 */
	public static function getNewWithUuid($uuid) {
		return new self($uuid);
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
	 * @param WorkerProcessFactory $factory
	 * @return $this
	 */
	public function workerProcessFactory(WorkerProcessFactory $factory) {
		$this->workerProcessFactory = $factory;
		return $this;
	}
	
	public function onStart() {
		
	}
	
	public function onTick() {
		if ($this->checkCommandReply()) {
			if ($command = $this->commandInput->getNextCommand()) {
				$this->processCommand($command);
			}
		}
	}
	
	public function processCommand(Command $command) {
		if ($command instanceof StartWorkerCommand) {
			$this->startWorker($command->getUuid());
			$this->commandInput->sendCommandReply('1');
			return;
		}
		
		if ($command instanceof StopWorkerCommand) {
			$this->stopWorker($command->getUuid());
			return;
		}
		
		throw new WorkerException('Unknown command: '.$command->getName());
	}
	
	/**
	 * Starting new worker process
	 * @param string $uuid
	 * @throws WorkerException
	 */
	public function startWorker($uuid) {
		if ($this->hasWorkerProcess($uuid)) {
			throw new WorkerException('Worker with that uuid is running: '.$uuid);
		}
		
		$this->workerProcessList[$uuid] = $this->workerProcessFactory->create(
		  WorkerInfo::newInfo()
			->uuid($uuid)
			->startTime(time())
		);
		
		$this->workerProcessList[$uuid]->start();
	}
	
	/**
	 * Stops all running workers
	 */
	public function stopAll() {
		foreach ($this->workerProcessList as $uuid => $workerProcess) {
			$this->stopWorker($uuid);
		}
	}
	
	public function stopWorker($uuid) {
		if ($this->hasWorkerProcess($uuid)) {
			$workerProcess = $this->getWorkerProcess($uuid);
			$workerProcess->getCommandOutput()->sendCommand(new StopWorkerCommand($uuid));
			$this->waitingCommandReplyProcess = $workerProcess;
		}
		
	}
	
	public function killAll() {
		foreach ($this->workerProcessList as $uuid => $workerProcess) {
			$this->killWorker($uuid);
		}
	}
	
	public function killWorker($uuid) {
		if ($this->hasWorkerProcess($uuid)) {
			$workerProcess = $this->getWorkerProcess($uuid);
			$workerProcess->kill();
		}
	}
	
	/**
	 * @param string $uuid
	 * @return WorkerProcess
	 */
	public function getWorkerProcess($uuid) {
		return $this->workerProcessList[$uuid];
	}
	
	/**
	 * @return WorkerProcess[]
	 */
	public function getWorkerProcessList() {
		return $this->workerProcessList;
	}
	
	/**
	 * @param string $uuid
	 * @return bool
	 */
	public function hasWorkerProcess($uuid) {
		return isset($this->workerProcessList[$uuid]);
	}
	
	protected function checkCommandReply() {
		if ($this->waitingCommandReplyProcess) {
			$workerCommandOutput = $this->waitingCommandReplyProcess->getCommandOutput();
			if ($reply = $workerCommandOutput->getCommandReply()) {
				$this->getCommandInput()->sendCommandReply($reply);
				$this->waitingCommandReplyProcess = null;
			}
			return false;
		}
		
		return true;
	}
	
	public function isWaitingForCommandReplyFromWorker() {
		return $this->waitingCommandReplyProcess != null;
	}
	
	public function checkWorkersHealth() {
		foreach ($this->workerProcessList as $workerProcess) {
			if ($workerProcess->getProcess()->isRunning()) {
				
			}
		}
	}
	
	public function onStop() {
		$this->stopAll();
	}
	
	public function onCommand(WorkerCommand $command) {
		
	}
	
	public function getUuid() {
		return $this->uuid;
	}
	
	/**
	 * @return CommandInput
	 */
	public function getCommandInput() {
		return $this->commandInput;
	}
	
}