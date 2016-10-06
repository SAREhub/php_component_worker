<?php

namespace SAREhub\Component\Worker;

/**
 * Context data for worker instance
 */
class WorkerContext {
	
	/**
	 * @var string
	 */
	private $uuid;
	
	/**
	 * @var string
	 */
	private $rootPath;
	
	/**
	 * @var array
	 */
	private $arguments = [];
	
	protected function __construct() { }
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param string $uuid
	 * @return $this
	 */
	public function withUuid($uuid) {
		$this->uuid = $uuid;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUuid() {
		return $this->uuid;
	}
	
	/**
	 * @param string $rootPath
	 * @return $this
	 */
	public function withRootPath($rootPath) {
		$this->$rootPath = $rootPath;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getRootPath() {
		return $this->rootPath;
	}
	
	/**
	 * @param array $arguments
	 * @return $this
	 */
	public function withArguments(array $arguments) {
		$this->arguments = $arguments;
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getArguments() {
		return $this->arguments;
	}
}