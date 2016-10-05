<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Implementation of command pattern
 */
class BasicCommand implements Command {
	
	private $name;
	private $parameters;
	
	public function __construct($name, array $parameters = null) {
		$this->name = $name;
		$this->parameters = empty($parameters) ? [] : $parameters;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return array
	 */
	public function getParameters() {
		return $this->parameters;
	}
}