<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Represents place for sending commands.
 */
interface CommandOutput {
	
	/**
	 * Sends command to output.
	 * @param $topic
	 * @param Command $command
	 * @param bool $wait
	 * @return
	 */
	public function send($topic, Command $command, $wait = false);
	
	/**
	 * Will close output of command
	 */
	public function close();
}