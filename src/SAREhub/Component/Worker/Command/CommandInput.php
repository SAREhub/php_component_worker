<?php

namespace SAREhub\Component\Worker\Command;

interface CommandInput {
	
	/**
	 * Gets next command from input or returns null when no command.
	 * @return Command|null
	 */
	public function getNextCommand();
	
	/**
	 * Sends reply for processed command
	 * @param string $reply
	 * @return
	 */
	public function sendCommandReply($reply);
}