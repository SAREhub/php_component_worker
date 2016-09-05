<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Represents place for sending commands.
 */
interface CommandOutput {
	
	/**
	 * Sends command to output.
	 * @param Command $command
	 * @return $this
	 */
	public function sendCommand(Command $command);
	
	/**
	 * Gets reply for sent command if available or return nulll.
	 * @return string|null
	 */
	public function getCommandReply();
}