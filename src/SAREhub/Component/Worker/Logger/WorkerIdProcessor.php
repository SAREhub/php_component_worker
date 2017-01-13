<?php

namespace SAREhub\Component\Worker\Logger;

/**
 * Processor for monolog logging, adds WorkerId to log records.
 */
class WorkerIdProcessor {
	
	private $workerId;
	
	public function __construct($workerId) {
		$this->workerId = $workerId;
	}
	
	public function __invoke($record) {
		$record['extra']['workerId'] = $this->workerId;
		return $record;
	}
}