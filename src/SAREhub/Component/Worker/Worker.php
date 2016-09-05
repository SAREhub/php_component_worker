<?php

namespace SAREhub\Component\Worker;
use SAREhub\Component\Worker\Command\WorkerCommand;

/**
 * Represents Worker instance.
 */
interface Worker {
	
	/**
	 * Executed once on worker start.
	 */
	public function onStart();
	
	/**
	 * Executed on every tick, must implements worker logic code.
	 */
	public function onTick();
	
	/**
	 * Executed when worker was stopped.
	 */
	public function onStop();
	
	/**
	 * Executed when command was received.
	 * @param WorkerCommand $command
	 * @return string command reply
	 */
	public function onCommand(WorkerCommand $command);
	
	/**
	 * @return string
	 */
	public function getUuid();
}