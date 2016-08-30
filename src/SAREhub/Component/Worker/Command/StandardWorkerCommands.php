<?php

namespace SAREhub\Component\Worker\Command;

class StandardWorkerCommands {
	
	const START_COMMAND_NAME = "command.worker.start";
	const STOP_COMMAND_NAME = "command.worker.stop";
	const KILL_COMMAND_NAME = "command.worker.kill";
	
	public static function startCommand(array $parameters) {
		return new WorkerCommand(self::START_COMMAND_NAME, $parameters);
	}
	
	public static function stopCommand(array $parameters) {
		return new WorkerCommand(self::STOP_COMMAND_NAME, $parameters);
	}
	
	public static function killCommand(array $parameters) {
		return new WorkerCommand(self::KILL_COMMAND_NAME, $parameters);
	}
	
}