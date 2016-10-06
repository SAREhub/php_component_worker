<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Represents place from where command can be gets.
 */
interface CommandInput {
	
	/**
	 * Gets next command from input or returns null when no command.
	 * @return Command|null
	 */
	public function getNextCommand();
	
	/**
	 * Sends reply for processed command
	 * @param string $reply
	 */
	public function sendCommandReply($reply);
}