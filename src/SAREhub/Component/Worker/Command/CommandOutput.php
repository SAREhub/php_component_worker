<?php

namespace SAREhub\Component\Worker\Command;

interface CommandOutput {
	
	/**
	 * Sends command to output.
	 * @param Command $command
	 */
	public function sendCommand(Command $command);
	
	/**
	 * Gets reply for sent command if available or return nulll.
	 * @return string|null
	 */
	public function getCommandReply();
}