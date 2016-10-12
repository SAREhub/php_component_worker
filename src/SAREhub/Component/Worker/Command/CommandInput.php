<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Represents place from where command can be gets.
 */
interface CommandInput {
	
	/**
	 * Gets next command from input or returns null when no command.
	 * @param bool $wait
	 * @return null|Command
	 */
	public function getNextCommand($wait = false);
	
	/**
	 * Will close command input
	 */
	public function close();
}