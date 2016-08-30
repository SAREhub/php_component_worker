<?php

namespace SAREhub\Component\Worker\Command;

class WorkerCommand implements \JsonSerializable {
	
	/** @var string */
	protected $name;
	/** @var array */
	protected $parameters;
	
	/**
	 * @param $name
	 * @param array|null $parameters
	 */
	public function __construct($name, array $parameters = null) {
		$this->name = $name;
		$this->parameters = ($parameters) ? $parameters : [];
	}
	
	public static function fromJson($json) {
		$decoded = json_decode($json, true);
		return new self($decoded['name'], $decoded['parameters']);
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
	
	public function jsonSerialize() {
		return [
		  'name' => $this->name,
		  'parameters' => $this->parameters
		];
	}
}