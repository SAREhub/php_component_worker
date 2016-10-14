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
	
	/**
	 * @param WorkerContext $context
	 * @return WorkerManager
	 */
	public static function newInstanceWithContext(WorkerContext $context) {
		return new self($context);
	}
	
	/**
	 * @param WorkerCommandService $service
	 * @return $this
	 */
	public function withCommandService(WorkerCommandService $service) {
		$this->commandService = $service;
		return $this;
	}
	
	/**
	 * @param WorkerProcessService $service
	 * @return $this
	 */
	public function withProcessService(WorkerProcessService $service) {
		$this->processService = $service;
		return $this;
	}
	
	protected function doStart() {
		$this->getProcessService()->start();
		$this->getCommandService()->start();
	}
	
	protected function doTick() {
		$this->getProcessService()->tick();
		$this->getCommandService()->tick();
	}
	
	protected function doStop() {
		foreach ($this->getWorkerList() as $workerId) {
			$this->doCommand(ManagerCommands::stop($workerId), function () { });
		}
		
		$this->getProcessService()->stop();
		$this->getCommandService()->stop();
	}
	
	protected function doCommand(Command $command, callable $replyCallback) {
		switch ($command->getName()) {
			case ManagerCommands::START:
				$this->onStartCommand($command, $replyCallback);
				break;
			case ManagerCommands::STOP:
				$this->onStopCommand($command, $replyCallback);
				break;
			default:
				$this->onUnknownCommand($command, $replyCallback);
				break;
		}
	}
	
	protected function onStartCommand(Command $command, callable $replyCallback) {
		$id = $command->getParameters()['id'];
		$context = ['command' => (string)$command];
		
		$reply = null;
		
		if ($this->getProcessService()->hasWorker($id)) {
			$message = 'worker with same id running';
			$this->getLogger()->warning($message, $context);
			$reply = CommandReply::error($command->getCorrelationId(), $message);
		} else {
			$this->getProcessService()->registerWorker($id);
			$message = 'worker started with PID: '.$this->getProcessService()->getWorkerPid($id);
			$this->getLogger()->info($message, $context);
			$reply = CommandReply::success($command->getCorrelationId(), $message);
		}
		
		$replyCallback($command, $reply);
	}
	
	protected function onStopCommand(Command $command, callable $replyCallback) {
		$id = $command->getParameters()['id'];
		$this->getLogger()->info('send stop command to worker', ['command' => (string)$command]);
		$manager = $this;
		$request = WorkerCommandRequest::newInstance()
		  ->withWorkerId($id)
		  ->withCommand(WorkerCommands::stop($command->getCorrelationId()))
		  ->withReplyCallback(
			function (WorkerCommandRequest $request, CommandReply $reply) use ($manager, $replyCallback) {
				$this->getLogger()->info('got reply', ['request' => $request, 'reply' => json_encode($reply)]);
				$manager->getProcessService()->unregisterWorker($request->getWorkerId());
				$replyCallback($reply);
			});
		$this->getCommandService()->process($request);
	}
	
	protected function onUnknownCommand(Command $command, callable $replyCallback) {
		$this->getLogger()->warning('unknown command', ['command' => (string)$command]);
		$replyCallback($command, CommandReply::error('unknown command', $command->getName()));
	}
	
	/**
	 * @return array
	 */
	public function getWorkerList() {
		return $this->getProcessService()->getWorkerList();
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