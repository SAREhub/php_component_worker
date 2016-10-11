<?php

namespace SAREhub\Component\Worker\Manager;

use SAREhub\Component\Worker\BasicWorker;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\WorkerCommands;
use SAREhub\Component\Worker\WorkerContext;

/**
 * Represents manager for workers.
 * It can start new workers, send commands to worker and kill selected worker when it needs.
 */
class WorkerManager extends BasicWorker {
	
	/**
	 * @var WorkerCommandService
	 */
	private $commandService;
	
	/**
	 * @var WorkerProcessService
	 */
	private $processService;
	
	public function __construct(WorkerContext $context) {
		parent::__construct($context);
	}
	
	public function withCommandService(WorkerCommandService $service) {
		$this->commandService = $service;
		return $this;
	}
	
	public function withProcessService(WorkerProcessService $service) {
		$this->processService = $service;
		return $this;
	}
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
		
	}
	
	protected function doStop() {
		$this->commandService->stop();
	}
	
	protected function doCommand(Command $command) {
		switch ($command->getName()) {
			case ManagerCommands::START:
				return $this->onStartCommand($command);
			case ManagerCommands::STOP:
				return $this->onStopCommand($command);
		}
		
		$this->getLogger()->warning('Unknown command ', ['command' => $command]);
		return CommandReply::error('unknown command', $command->getName());
	}
	
	protected function onStartCommand(Command $command) {
		$id = $command->getParameters()['id'];
		$context = ['command' => $command];
		
		if ($this->getProcessService()->has($id)) {
			$message = 'worker with same id running';
			$this->getLogger()->warning($message, $context);
			return CommandReply::error($message);
		}
		
		$this->getProcessService()->register($id);
		$this->getProcessService()->start($id);
		$this->getCommandService()->register($id);
		
		$message = 'worker started';
		$this->getLogger()->info($message, $context);
		return CommandReply::success($message);
	}
	
	protected function onStopCommand(Command $command) {
		$id = $command->getParameters()['id'];
		$this->getLogger()->info('send stop command to worker', ['command' => $command]);
		
		$reply = $this->getCommandService()->sendCommand($id, WorkerCommands::stop());
		if ($reply->isSuccess()) {
			$this->getProcessService()->unregister($id);
			$this->getCommandService()->unregister($id);
		}
		return $reply;
	}
	
	/**
	 * @return WorkerProcessService
	 */
	protected function getProcessService() {
		return $this->processService;
	}
	
	/**
	 * @return WorkerCommandService
	 */
	protected function getCommandService() {
		return $this->commandService;
	}
}