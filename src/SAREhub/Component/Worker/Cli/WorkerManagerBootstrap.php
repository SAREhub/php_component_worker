<?php

namespace SAREhub\Component\Worker\Cli;

use Monolog\Logger;
use SAREhub\Commons\Misc\Parameters;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\CommandReplyOutput;
use SAREhub\Component\Worker\Command\CommandService;
use SAREhub\Component\Worker\Command\JsonCommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandInput;
use SAREhub\Component\Worker\Command\ZmqCommandOutput;
use SAREhub\Component\Worker\Command\ZmqCommandReplyInput;
use SAREhub\Component\Worker\Command\ZmqCommandReplyOutput;
use SAREhub\Component\Worker\Manager\WorkerManager;
use SAREhub\Component\Worker\Manager\WorkerProcessFactory;
use SAREhub\Component\Worker\Manager\WorkerProcessService;
use SAREhub\Component\Worker\WorkerContext;
use SAREhub\Component\Worker\WorkerRunner;

class WorkerManagerBootstrap {
	
	/**
	 * @var WorkerContext
	 */
	private $workerContext;
	
	/**
	 * @var Parameters
	 */
	private $config;
	
	/**
	 * @var \ZMQContext
	 */
	private $zmqContext;
	
	protected function __construct() {
		$this->zmqContext = new \ZMQContext();
	}
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param WorkerContext $context
	 * @return $this
	 */
	public function withWorkerContext(WorkerContext $context) {
		$this->workerContext = $context;
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
	 * @return WorkerRunner
	 */
	public function build() {
		return $this->createRunner();
	}
	
	/**
	 * @return WorkerRunner
	 */
	private function createRunner() {
		$runner = WorkerRunner::newInstance()
		  ->withWorker($this->createManager())
		  ->withCommandInput($this->createRunnerCommandInput())
		  ->withCommandReplyOutput($this->createRunnerCommandReplyOutput())
		  ->usePcntl();
		$runner->setLogger($this->createLogger('runner'));
		return $runner;
	}
	
	/**
	 * @return CommandInput
	 */
	private function createRunnerCommandInput() {
		$config = $this->getRunnerConfig()->getRequiredAsMap('commandInput');
		return ZmqCommandInput::newInstance()
		  ->withCommandSubscriber(Subscriber::inContext($this->zmqContext)
		    ->subscribe($this->getConfig()->getRequired('id'))
			->connect($config->getRequired('endpoint'))
		  )->withCommandFormat(JsonCommandFormat::newInstance());
	}
	
	/**
	 * @return CommandReplyOutput
	 */
	private function createRunnerCommandReplyOutput() {
		$config = $this->getRunnerConfig()->getRequiredAsMap('commandReplyOutput');
		return ZmqCommandReplyOutput::newInstance()
		  ->withPublishTopic($config->getRequired('topic'))
		  ->withPublisher(Publisher::inContext($this->zmqContext)
			->connect($config->getRequired('endpoint'))
		  );
	}
	
	/**
	 * @return WorkerManager
	 */
	private function createManager() {
		$manager = WorkerManager::newInstanceWithContext($this->workerContext)
		  ->withProcessService($this->createManagerProcessService())
		  ->withCommandService($this->createManagerCommandService());
		$manager->setLogger($this->createLogger('manager'));
		return $manager;
	}
	
	private function createManagerProcessService() {
		$config = $this->getManagerConfig()->getRequiredAsMap('processService');
		return WorkerProcessService::newInstance()
		  ->withWorkerProcessFactory(WorkerProcessFactory::newInstance()
			->withRunnerScriptPath($config->getRequired('runnerScript'))
			->withArguments($config->getRequired('arguments'))
			->withWorkingDirectory($config->getRequired('workingDirectory'))
		  );
	}
	
	private function createManagerCommandService() {
		return CommandService::newInstance()
		  ->withCommandOutput($this->createManagerCommandServiceCommandOutput())
		  ->withCommandReplyInput($this->createManagerCommandServiceCommandReplyInput());
	}
	
	private function createManagerCommandServiceCommandOutput() {
		$config = $this->getManagerConfig()
		  ->getRequiredAsMap('commandService')
		  ->getRequiredAsMap('commandOutput');
		return ZmqCommandOutput::newInstance()
		  ->withPublisher(Publisher::inContext($this->zmqContext)
			->bind($config->getRequired('endpoint'))
		  )->withCommandFormat(JsonCommandFormat::newInstance());
	}
	
	private function createManagerCommandServiceCommandReplyInput() {
		$config = $this->getManagerConfig()
		  ->getRequiredAsMap('commandService')
		  ->getRequiredAsMap('commandReplyInput');
		
		return ZmqCommandReplyInput::newInstance()
		  ->withSubscriber(Subscriber::inContext($this->zmqContext)
			->subscribe($config->getRequired('topic'))
			->bind($config->get('endpoint'))
		  );
	}
	
	private function createLogger($name) {
		$config = $this->getLoggingConfig();
		return new Logger($name, $config->getRequired('handlers'), $config->getRequired('processors'));
	}
	
	/**
	 * @return Parameters
	 */
	private function getManagerConfig() {
		return $this->getConfig()->getRequiredAsMap('manager');
	}
	
	/**
	 * @return Parameters
	 */
	private function getRunnerConfig() {
		return $this->getConfig()->getRequiredAsMap('runner');
	}
	
	/**
	 * @return Parameters
	 */
	private function getLoggingConfig() {
		return $this->getConfig()->getRequiredAsMap('logging');
	}
	
	private function getConfig() {
		return $this->config;
	}
}