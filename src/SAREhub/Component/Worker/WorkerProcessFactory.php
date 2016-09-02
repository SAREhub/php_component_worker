<?php

namespace SAREhub\Component\Worker;

use SAREhub\Component\Worker\Command\ProcessStreamCommandOutput;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class WorkerProcessFactory {
	
	protected $runnerScript;
	protected $workingDirectory = null;
	protected $commandOutputFactory;
	protected $arguments = [];
	
	/**
	 * @param string $runnerScript
	 * @return WorkerProcessFactory
	 */
	public static function getForRunnerScript($runnerScript) {
		$factory = new self();
		$factory->runnerScript = $runnerScript;
		return $factory;
	}
	
	/**
	 * Sets working directory for created worker processes.
	 * @param string $workingDirectory
	 * @return $this
	 */
	public function workingDirectory($workingDirectory) {
		$this->workingDirectory = (string)$workingDirectory;
		return $this;
	}
	
	/**
	 * @param array $arguments
	 * @return $this
	 */
	public function arguments(array $arguments) {
		$this->arguments = $arguments;
		return $this;
	}
	
	/**
	 * ```php
	 * function (Process $process) {
	 *    return $commandOutput;
	 * }
	 * ```
	 * @param callable $commandOutputFactory
	 * @return $this
	 */
	public function commandOutputFactory(callable $commandOutputFactory) {
		$this->commandOutputFactory = $commandOutputFactory;
		return $this;
	}
	
	/**
	 * @return \Closure
	 */
	public static function getDefaultCommandOutputFactory() {
		return function (Process $process) {
			$process->setInput(new InputStream());
			return ProcessStreamCommandOutput::getForProcess($process);
		};
	}
	
	/**
	 * @param WorkerInfo $workerInfo
	 * @return WorkerProcess
	 */
	public function create(WorkerInfo $workerInfo) {
		$standardArguments = ['php', $this->runnerScript, $workerInfo->uuid];
		$processArguments = array_merge($standardArguments, $this->arguments);
		$process = ProcessBuilder::create($processArguments)
		  ->setWorkingDirectory($this->workingDirectory)
		  ->getProcess();
		
		$commandOutputFactory = $this->commandOutputFactory;
		return WorkerProcess::getFor($workerInfo, $process)
		  ->commandOutput($commandOutputFactory($process));
	}
}