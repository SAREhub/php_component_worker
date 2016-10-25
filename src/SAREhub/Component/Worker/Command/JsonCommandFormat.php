<?php

namespace SAREhub\Component\Worker\Command;

class JsonCommandFormat implements CommandFormat {
	
	const CORRELATION_ID_INDEX = 'correlation_id';
	const NAME_INDEX = 'name';
	const PARAMETERS_INDEX = 'parameters';
	
	/**
	 * @return JsonCommandFormat
	 */
	public static function newInstance() {
		return new self();
	}
	
	public function marshal(Command $command) {
		return json_encode([
		  self::CORRELATION_ID_INDEX => $command->getCorrelationId(),
		  self::NAME_INDEX => $command->getName(),
		  self::PARAMETERS_INDEX => $command->getParameters()
		]);
	}
	
	public function unmarshal($commandData) {
		$commandData = json_decode($commandData, true);
		return new BasicCommand(
		  $commandData[self::CORRELATION_ID_INDEX],
		  $commandData[self::NAME_INDEX],
		  $commandData[self::PARAMETERS_INDEX]
		);
	}
}