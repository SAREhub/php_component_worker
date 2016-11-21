<?php

namespace SAREhub\Component\Worker\Cli;


use Monolog\Handler\SyslogHandler;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Respect\Validation\Validator;
use SAREhub\Commons\Misc\Dsn;

class CliWorkerManagerConfigBuilder {
	
	/**
	 * @var string
	 */
	private $id;
	
	/**
	 * @var string
	 */
	private $scriptPath;
	
	/**
	 * @var string
	 */
	private $managerCommandServiceZmqRootPath;
	
	/**
	 * @var callable
	 */
	private $onStart;
	
	private $loggingConfigFactory;
	
	
	/**
	 * @return CliWorkerManagerConfigBuilder
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param string $id
	 * @return CliWorkerManagerConfigBuilder
	 */
	public function withId($id) {
		$this->id = $id;
		return $this;
	}
	
	/**
	 * @param string $scriptPath
	 * @return CliWorkerManagerConfigBuilder
	 */
	public function withScriptPath($scriptPath) {
		$this->scriptPath = $scriptPath;
		return $this;
	}
	
	/**
	 * @param string $managerCommandServiceZmqRootPath
	 * @return CliWorkerManagerConfigBuilder
	 */
	public function withManagerCommandServiceZmqRootPath($managerCommandServiceZmqRootPath) {
		$this->managerCommandServiceZmqRootPath = $managerCommandServiceZmqRootPath;
		return $this;
	}
	
	/**
	 * @param callable $callback
	 * @return $this
	 */
	public function withOnStart(callable $callback) {
		$this->onStart = $callback;
		return $this;
	}
	
	/**
	 * @param callable $factory
	 * @return $this
	 */
	public function withLoggingConfigFactory(callable $factory) {
		$this->loggingConfigFactory = $factory;
		return $this;
	}
	
	public static function getDefaultLoggingFactory() {
		return function ($managerId) {
			return [
			  'handlers' => [
				new SyslogHandler($managerId, LOG_USER, Logger::INFO)
			  ],
			  'processors' => [
				new PsrLogMessageProcessor()
			  ]
			];
		};
	}
	
	public function create() {
		$this->checkSetup();
		return [
		  'id' => $this->id,
		  'manager' => $this->createManagerConfig(),
		  'onStart' => $this->onStart,
		  'logging' => $this->createLoggingConfig()
		];
	}
	
	private function createManagerConfig() {
		return [
		  'processService' => $this->createManagerProcessServiceConfig(),
		  'commandService' => $this->createManagerCommandServiceConfig()
		];
	}
	
	private function createManagerProcessServiceConfig() {
		return [
		  'runnerScript' => $this->scriptPath,
		  'arguments' => [],
		  'workingDirectory' => './'
		];
	}
	
	private function createManagerCommandServiceConfig() {
		$zmqPath = $this->managerCommandServiceZmqRootPath.'/'.$this->id;
		return [
		  'commandOutput' => [
		    'endpoint' => Dsn::ipc()->endpoint($zmqPath.'/commandInput.sock')
		  ],
		  'commandReplyInput' => [
			'topic' => 'worker.command.reply',
		    'endpoint' => Dsn::ipc()->endpoint($zmqPath.'/commandReplyOutput.sock')
		  ]
		];
	}
	
	private function createLoggingConfig() {
		$factory = $this->loggingConfigFactory;
		return $factory($this->id);
	}
	
	private function checkSetup() {
		$v = Validator::notEmpty();
		$v->setName('id')->assert($this->id);
		$v->setName('scriptPath')->assert($this->scriptPath);
		$v->setName('managerCommandServiceZmqRootPath')->assert($this->managerCommandServiceZmqRootPath);
		$v->setName('onStart')->assert($this->onStart);
		$v->setName('loggingFactory')->assert($this->loggingConfigFactory);
	}
	
}