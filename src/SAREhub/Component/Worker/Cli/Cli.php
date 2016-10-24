<?php

namespace SAREhub\Component\Worker\Cli;

use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Command\CommandService;
use Symfony\Component\Console\Application;

class Cli {
	
	/**
	 * @var Application
	 */
	private $application;
	
	/**
	 * @var CommandService
	 */
	private $commandService;
	
	/**
	 * @var string
	 */
	private $sessionId;
	
	/**
	 * @var Parameters
	 */
	private $config;
	private $loggerFactory;
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Application $application
	 * @return $this
	 */
	public function withApplication(Application $application) {
		$this->application = $application;
		return $this;
	}
	
	/**
	 * @param CommandService $service
	 * @return $this
	 */
	public function withCommandService(CommandService $service) {
		$this->commandService = $service;
		return $this;
	}
	
	/**
	 * @param string $id
	 * @return $this
	 */
	public function withSessionId($id) {
		$this->sessionId = $id;
		return $this;
	}
	
	/**
	 * @param Parameters $config
	 * @return $this
	 */
	public function withConfig(Parameters $config) {
		$this->config = $config;
		return $this;
	}
	
	/**
	 * @param callable $factory
	 * @return $this
	 */
	public function withLoggerFactory(callable $factory) {
		$this->loggerFactory = $factory;
		return $this;
	}
 	
	public function run() {
		$this->getCommandService()->start();
		$this->getApplication()->run();
		$this->getCommandService()->stop();
	}
	
	public function registerCommand(CliCommand $command) {
		$command->withCli($this);
		$command->setLogger($this->getCommandLogger($command));
		$this->getApplication()->add($command);
		
		return $this;
	}
	
	public function getManagerConfigFilePath($managerId) {
		$configRootPath = $this->getConfig()->getRequiredAsMap('manager')->getRequired('configRootPath');
		return $configRootPath.'/'.$managerId.'.php';
	}
	
	public function isManagerConfigFileExists($managerId) {
		$filePath = $this->getManagerConfigFilePath($managerId);
		return file_exists($filePath);
	}
	
	private function getCommandLogger(CliCommand $command) {
		return ($this->loggerFactory)($command->getName());
	}
	
	/**
	 * @return Application
	 */
	public function getApplication() {
		return $this->application;
	}
	
	/**
	 * @return CommandService
	 */
	public function getCommandService() {
		return $this->commandService;
	}
	
	/**
	 * @return string
	 */
	public function getSessionId() {
		return $this->sessionId;
	}
	
	/**
	 * @return Parameters
	 */
	public function getConfig() {
		return $this->config;
	}
}