<?php

namespace SAREhub\Component\Worker\Cli;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SAREhub\Commons\Misc\Parameters;
use Symfony\Component\Console\Command\Command;

abstract class CliCommand extends Command implements LoggerAwareInterface {
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * @var Cli
	 */
	private $cli;
	
	
	public function __construct($name = null) {
		parent::__construct($name);
		$this->logger = new NullLogger();
	}
	
	/**
	 * @param Cli $cli
	 * @return $this
	 */
	public function withCli(Cli $cli) {
		$this->cli = $cli;
		return $this;
	}
	
	/**
	 * @return Cli
	 */
	public function getCli() {
		return $this->cli;
	}
	
	/**
	 * @return Parameters
	 */
	public function getCliConfig() {
		return $this->getCli()->getConfig();
	}
	
	/**
	 * @return LoggerInterface
	 */
	public function getLogger() {
		return $this->logger;
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}