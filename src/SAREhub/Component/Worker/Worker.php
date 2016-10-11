<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Service\Service;

/**
 * Represents Worker instance.
 */
interface Worker extends Service {
	
	/**
	 * Executed when command was received.
	 * @param Command $command
	 * @return CommandReply command reply
	 */
	public function processCommand(Command $command);
	
	/**
	 * @return string
	 */
	public function getId();
	
	/**
	 * @return WorkerContext
	 */
	public function getContext();
}