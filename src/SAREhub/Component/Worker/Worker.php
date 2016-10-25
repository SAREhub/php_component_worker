<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Service\Service;

/**
 * Represents Worker instance.
 */
interface Worker extends Service {
	
	/**
	 * Executed when command was received.
	 * @param Command $command
	 * @param callable $replyCallback
	 */
	public function processCommand(Command $command, callable $replyCallback);
	
	/**
	 * @return string
	 */
	public function getId();
	
	/**
	 * @return WorkerContext
	 */
	public function getContext();
}