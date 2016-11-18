<?php

namespace SAREhub\Component\Worker\Cli;

use SAREhub\Commons\Misc\Parameters;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\Command\CommandService;
use SAREhub\Component\Worker\Command\JsonCommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandOutput;
use SAREhub\Component\Worker\Command\ZmqCommandReplyInput;
use Symfony\Component\Console\Application;

class CliBootstrap {
	
	/**
	 * @var Parameters
	 */
	private $config;
	
	/**
	 * @var \ZMQContext
	 */
	private $zmqContext;
	
	/**
	 * @var Application
	 */
	private $application;
	
	private $loggerFactory;
	
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Parameters $config
	 * @return CliBootstrap
	 */
	public function withConfig($config) {
		$this->config = $config;
		return $this;
	}
	
	/**
	 * @param \ZMQContext $context
	 * @return $this
	 */
	public function withZmqContext(\ZMQContext $context) {
		$this->zmqContext = $context;
		return $this;
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
	 * @param callable $loggerFactory
	 * @return CliBootstrap
	 */
	public function withLoggerFactory($loggerFactory) {
		$this->loggerFactory = $loggerFactory;
		return $this;
	}
	
	public function run() {
		$sessionId = uniqid(mt_rand(10000, 100000).time());
		$cli = Cli::newInstance()
		  ->withApplication($this->application)
		  ->withConfig($this->config)
		  ->withSessionId($sessionId)
		  ->withLoggerFactory($this->loggerFactory)
		  ->withCommandService($this->createCommandService());
		$this->registerStandardCommands($cli);
		$cli->run();
	}
	
	private function createCommandService() {
		return CommandService::newInstance()
		  ->withCommandOutput($this->createCommandServiceOutput())
		  ->withCommandReplyInput($this->createCommandServiceInput());
	}
	
	private function createCommandServiceOutput() {
		$config = $this->config->getRequiredAsMap('commandService');
		return ZmqCommandOutput::newInstance()
		  ->withPublisher(Publisher::inContext($this->zmqContext)
			->connect($config->getRequiredAsMap('commandOutput')->getRequired('endpoint')))
		  ->withCommandFormat(JsonCommandFormat::newInstance());
	}
	
	private function createCommandServiceInput() {
		$config = $this->config->getRequiredAsMap('commandService')->getRequiredAsMap('commandReplyInput');
		return ZmqCommandReplyInput::newInstance()
		  ->withSubscriber(Subscriber::inContext($this->zmqContext)
			->connect($config->getRequired('endpoint'))
			->subscribe($config->getRequired('topic'))
		  );
	}
	
	private function registerStandardCommands(Cli $cli) {
		$cli->registerCommand(StartManagerCommand::newInstance()->withSystemdHelper(new SystemdHelper()))
		  ->registerCommand(StartWorkerCommand::newInstance())
		  ->registerCommand(StopManagerCommand::newInstance())
		  ->registerCommand(StopWorkerCommand::newInstance());
	}
}