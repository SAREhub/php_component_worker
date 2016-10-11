<?php

namespace SAREhub\Component\Worker\Service;

interface Service {
	
	/**
	 * Executed for start service.
	 * @throws \Exception When something was wrong.
	 */
	public function start();
	
	/**
	 * Executed on every service tick.
	 * @throws \Exception When something was wrong.
	 */
	public function tick();
	
	/**
	 * Executed for stop service
	 * @throws \Exception When something was wrong.
	 */
	public function stop();
	
	/**
	 * @return boolean
	 */
	public function isStarted();
	
	/**
	 * @return boolean
	 */
	public function isStopped();
	
	/**
	 * @return boolean
	 */
	public function isRunning();
}