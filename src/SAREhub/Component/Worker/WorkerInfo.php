<?php

namespace SAREhub\Component\Worker;

/**
 * Represents basic information about worker
 */
class WorkerInfo {
	
	public $uuid;
	public $startTime;
	
	/**
	 * @return WorkerInfo
	 */
	public static function newInfo() {
		$info = new self();
		return $info;
	}
	
	/**
	 * @param string $uuid
	 * @return $this
	 */
	public function uuid($uuid) {
		$this->uuid = $uuid;
		return $this;
	}
	
	/**
	 * @param string $time
	 * @return $this
	 */
	public function startTime($time) {
		$this->startTime = $time;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function toArray() {
		return [
		  'uuid' => $this->uuid,
		  'startTime' => $this->startTime
		];
	}
}