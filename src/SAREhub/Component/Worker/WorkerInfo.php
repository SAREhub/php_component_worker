<?php

namespace SAREhub\Component\Worker;

/**
 * Represents basic information about worker
 */
class WorkerInfo {
	
	public $uuid;
	public $startTime;
	
	public function toArray() {
		return [
		  'uuid' => $this->uuid,
		  'startTime' => $this->startTime
		];
	}
}