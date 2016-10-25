<?php

namespace SAREhub\Component\Worker\Manager;


use SAREhub\Component\Worker\Command\BasicCommand;

class ManagerCommands {
	
	const COMMAND_NAME_PREFIX = 'worker.manager.';
	
	const CHECK_HEALTH = self::COMMAND_NAME_PREFIX.'check_health';
	
	const START = self::COMMAND_NAME_PREFIX.'start';
	const STOP = self::COMMAND_NAME_PREFIX.'stop';
	const STOP_ALL = self::COMMAND_NAME_PREFIX.'stop_all';
	const RELOAD = self::COMMAND_NAME_PREFIX.'reload';
	
	const PAUSE = self::COMMAND_NAME_PREFIX.'pause';
	const RESUME = self::COMMAND_NAME_PREFIX.'resume';
	
	const KILL = self::COMMAND_NAME_PREFIX.'kill';
	
	const STATUS = self::COMMAND_NAME_PREFIX.'status';
	const INFO = self::COMMAND_NAME_PREFIX.'info';
	const STATS = self::COMMAND_NAME_PREFIX.'stats';
	
	const CUSTOM = self::COMMAND_NAME_PREFIX.'custom';
	
	/**
	 * @param $correltionId
	 * @param $workerId
	 * @return BasicCommand
	 */
	public static function start($correltionId, $workerId) {
		return new BasicCommand($correltionId, self::START, ['id' => $workerId]);
	}
	
	/**
	 * @param $correltionId
	 * @param $workerId
	 * @return BasicCommand
	 */
	public static function stop($correltionId, $workerId) {
		return new BasicCommand($correltionId, self::STOP, ['id' => $workerId]);
	}
	
	/**
	 * @param $correltionId
	 * @return BasicCommand
	 */
	public static function stopAll($correltionId) {
		return new BasicCommand($correltionId, self::STOP_ALL);
	}
	
	public static function custom($correltionId, $workerId, array $commandData) {
		return new BasicCommand($correltionId, self::CUSTOM, [
		  'id' => $workerId,
		  'command' => $commandData
		]);
	}
}