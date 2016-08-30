<?php

namespace SAREhub\Component\Worker\Command;

interface WorkerCommandInput {
	
	/**
	 * Gets next command from input or returns null when no command.
	 * @return WorkerCommand|null
	 */
	public function getNextCommand();
	
	/**
	 * Sends ack. for processed command.
	 */
	public function sendCommandConfirmation();
}