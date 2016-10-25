<?php

namespace SAREhub\Component\Worker\Command;

/**
 * Implementation of command pattern
 */
class BasicCommand implements Command {
	
	private $correlationId;
	private $name;
	private $parameters;
	
	public function __construct($correlationId, $name, array $parameters = null) {
		$this->correlationId = $correlationId;
		$this->name = $name;
		$this->parameters = empty($parameters) ? [] : $parameters;
	}
	
	/**
	 * @return string
	 */
	public function getCorrelationId() {
		return $this->correlationId;
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
	
	public function __toString() {
		return 'COMMAND:'.json_encode([
		  'correlation_id' => $this->getCorrelationId(),
		  'name' => $this->getName(),
		  'parameters' => $this->getParameters()
		]);
	}
}