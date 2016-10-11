<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Service\ServiceSupport;

/**
 * Worker implementation with auto handle lifecycle and logging support.
 * Use that class for create concrete worker with logic.
 */
abstract class BasicWorker extends ServiceSupport implements Worker {
	
	/**
	 * @var WorkerContext
	 */
	private $context;
	
	public function __construct(WorkerContext $context) {
		$this->context = $context;
	}
	
	public function processCommand(Command $command) {
		$this->getLogger()->info('execute command: '.$command);
		return $this->doCommand($command);
	}
	
	/**
	 * Contains custom command processing logic.
	 *
	 * @param Command $command
	 * @throws WorkerException When something was wrong.
	 */
	protected abstract function doCommand(Command $command);
	
	public function getId() {
		return $this->getContext()->getId();
	}
	
	public function getContext() {
		return $this->context;
	}
}