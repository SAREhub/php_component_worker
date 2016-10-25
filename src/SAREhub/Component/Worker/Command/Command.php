<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Base interface for all commands
 */
interface Command {
	
	public function getCorrelationId();
	
	/**
	 * @return string
	 */
	public function getName();
	
	/**
	 * @return array
	 */
	public function getParameters();
	
	public function __toString();
}