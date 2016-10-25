<?php

namespace SAREhub\Component\Worker\Command;

interface CommandFormat {
	
	/**
	 * @param Command $command
	 * @return mixed
	 */
	public function marshal(Command $command);
	
	/**
	 * @param $commandData
	 * @return Command
	 */
	public function unmarshal($commandData);
}