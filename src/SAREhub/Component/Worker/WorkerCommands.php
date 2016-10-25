<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\BasicCommand;

class WorkerCommands {
	
	const STOP = 'worker.stop';
	
	/**
	 * @param $correlationId
	 * @return BasicCommand
	 */
	public static function stop($correlationId) {
		return new BasicCommand($correlationId, self::STOP);
	}
}