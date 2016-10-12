<?php

namespace SAREhub\Component\Worker\Command;

interface CommandReplyOutput {
	
	/**
	 * Sends reply for command
	 * @param Command $command
	 * @param CommandReply $reply
	 * @param bool $wait
	 * @return
	 */
	public function send(Command $command, CommandReply $reply, $wait = false);
	
	/**
	 * Will close command input
	 */
	public function close();
}