<?php

namespace SAREhub\Component\Worker\Manager;

use Respect\Validation\Validator as v;
use Symfony\Component\Process\ProcessBuilder;

class WorkerProcessFactory {
	
	/**
	 * @var string
	 */
	private $runnerScriptPath = null;
	
	/**
	 * @var string
	 */
	private $workingDirectory = null;
	
	/**
	 * @var array
	 */
	private $arguments = [];
	
	protected function __construct() {
		
	}
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param string $path
	 * @return $this
	 */
	public function withRunnerScriptPath($path) {
		$this->runnerScriptPath = $path;
		return $this;
	}
	
	/**
	 * @param string $dir
	 * @return $this
	 */
	public function withWorkingDirectory($dir) {
		$this->workingDirectory = $dir;
		return $this;
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
	 * @param string $workerId
	 * @return WorkerProcess
	 */
	public function create($workerId) {
		v::notEmpty()->setName('runnerScriptPath')->check($this->runnerScriptPath);
		
		$standardArguments = ['php', $this->runnerScriptPath, $workerId];
		$processArguments = array_merge($standardArguments, $this->arguments);
		$process = ProcessBuilder::create($processArguments)
		  ->setWorkingDirectory($this->workingDirectory)
		  ->getProcess();
		return new WorkerProcess($workerId, $process);
	}
}