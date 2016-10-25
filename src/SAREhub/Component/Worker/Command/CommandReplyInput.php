<?php

namespace SAREhub\Component\Worker\Command;


interface CommandReplyInput {
	
	/**
	 * @param bool $wait
	 * @return CommandReply
	 */
	public function getNext($wait = false);
	
	public function close();
}