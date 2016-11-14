<?php

namespace SAREhub\Component\Worker;

use Respect\Validation\Validator;
use SAREhub\Component\Worker\Command\ZmqCommandInputServiceFactory;

class ZmqWorkerRunnerBootstrap {
	
	/**
	 * @var Worker
	 */
	private $worker;
	/**
	 * @var ZmqCommandInputServiceFactory
	 */
	private $commandInputServiceFactory;
	private $endpointPrefix;
	
	private $loggerFactory;
	
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param Worker $worker
	 * @return $this
	 */
	public function withWorker(Worker $worker) {
		$this->worker = $worker;
		return $this;
	}
	
	/**
	 * @param ZmqCommandInputServiceFactory $factory
	 * @return $this
	 */
	public function withCommandInputServiceFactory(ZmqCommandInputServiceFactory $factory) {
		$this->commandInputServiceFactory = $factory;
		return $this;
	}
	
	/**
	 * @param $prefix
	 * @return $this
	 */
	public function withEndpointPrefix($prefix) {
		$this->endpointPrefix = $prefix;
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
	
	/**
	 * @return WorkerRunner
	 */
	public function create() {
		$this->checkSetup();
		$commandInputServiceFactory = $this->getCommandInputServiceFactory();
		$runner = WorkerRunner::newInstance()
		  ->withWorker($this->worker)
		  ->usePcntl()
		  ->withCommandInput($commandInputServiceFactory->createCommandInput())
		  ->withCommandReplyOutput($commandInputServiceFactory->createCommandReplyOutput());
		$this->registerLoggers($runner);
		return $runner;
	}
	
	private function checkSetup() {
		$v = Validator::notEmpty();
		$v->setName('worker')->assert($this->worker);
		$v->setName('endpointPrefix')->assert($this->endpointPrefix);
		$v->setName('commandInputServiceFactory')->assert($this->commandInputServiceFactory);
		
	}
	
	private function getCommandInputServiceFactory() {
		return $this->commandInputServiceFactory
		  ->withCommandInputTopic($this->worker->getId())
		  ->withEndpointPrefix($this->endpointPrefix);
	}
	
	private function registerLoggers(WorkerRunner $runner) {
		if ($this->loggerFactory) {
			$workerId = $this->worker->getId();
			$runner->setLogger($this->createLogger('runner_'.$workerId));
			$this->worker->setLogger($this->createLogger('worker_'.$workerId));
		}
	}
	
	private function createLogger($name) {
		$factory = $this->loggerFactory;
		return $factory($name);
	}
}