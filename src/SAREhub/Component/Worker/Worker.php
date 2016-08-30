<?php

namespace SAREhub\Component\Worker;

/**
 * Represents Worker instance.
 */
abstract class Worker {
	
	/** @var WorkerInfo */
	protected $info;
	
	public function __construct(WorkerInfo $info) {
		$this->info = $info;
	}
	
	/**
	 * Implements worker work logic code.
	 */
	public abstract function work();
	
	/**
	 * Executed when worker was stopped.
	 * @param array $parameters Optional array with parameters.
	 */
	public abstract function onStop(array $parameters);
	
	/**
	 * @return WorkerInfo
	 */
	public function getInfo() {
		return $this->info;
	}
}