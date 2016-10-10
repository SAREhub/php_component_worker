<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\BasicCommand;

class WorkerCommands {
	
	const STOP = 'worker.stop';
	
	/**
	 * @return BasicCommand
	 */
	public static function stop() {
		return new BasicCommand(self::STOP);
	}
}