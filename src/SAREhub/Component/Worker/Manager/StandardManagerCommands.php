<?php

namespace SAREhub\Component\Worker\Manager;


class StandardManagerCommands {
	
	const COMMAND_NAME_PREFIX = 'worker.manager.';
	
	const CHECK_HEALTH = self::COMMAND_NAME_PREFIX.'check_health';
	
	const START = self::COMMAND_NAME_PREFIX.'start';
	const STOP = self::COMMAND_NAME_PREFIX.'stop';
	const RELOAD = self::COMMAND_NAME_PREFIX.'reload';
	
	const PAUSE = self::COMMAND_NAME_PREFIX.'pause';
	const RESUME = self::COMMAND_NAME_PREFIX.'resume';
	
	const KILL = self::COMMAND_NAME_PREFIX.'kill';
	
	const STATUS = self::COMMAND_NAME_PREFIX.'status';
	const INFO = self::COMMAND_NAME_PREFIX.'info';
	const STATS = self::COMMAND_NAME_PREFIX.'stats';
}