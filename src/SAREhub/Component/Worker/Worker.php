<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\Command;

/**
 * Represents Worker instance.
 */
interface Worker {
	
	/**
	 * Executed for start worker
	 * @throws WorkerException When something was wrong.
	 */
	public function start();
	
	/**
	 * Executed on every tick, must implements worker logic code.
	 * @throws WorkerException When something was wrong.
	 */
	public function tick();
	
	/**
	 * Executed for stop worker
	 * @throws WorkerException When something was wrong.
	 */
	public function stop();
	
	/**
	 * Executed when command was received.
	 * @param Command $command
	 * @return mixed command reply
	 * @throws WorkerException When something was wrong.
	 */
	public function processCommand(Command $command);
	
	/**
	 * @return boolean
	 */
	public function isStarted();
	
	/**
	 * @return boolean
	 */
	public function isStopped();
	
	/**
	 * @return string
	 */
	public function getUuid();
	
	/**
	 * @return WorkerContext
	 */
	public function getContext();
}