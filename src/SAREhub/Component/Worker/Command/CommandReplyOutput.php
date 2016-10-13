<?php

namespace SAREhub\Component\Worker\Command;

interface CommandReplyOutput {
	
	/**
	 * Sends reply for command
	 * @param CommandReply $reply
	 * @param bool $wait
	 * @return
	 */
	public function send(CommandReply $reply, $wait = false);
	
	/**
	 * Will close command input
	 */
	public function close();
}