<?php

namespace SAREhub\Component\Worker\Manager;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\CommandOutputFactory;
use SAREhub\Component\Worker\Command\CommandReply;

class WorkerCommandService implements LoggerAwareInterface {
	
	const DEFAULT_COMMAND_REPLY_TIMEOUT = 30;
	
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	
	/**
	 * @var CommandOutputFactory
	 */
	private $outputFactory;
	
	/**
	 * @var CommandOutput[]
	 */
	private $outputList = [];
	
	private $commandReplyTimeout = self::DEFAULT_COMMAND_REPLY_TIMEOUT;
	
	public function __construct(CommandOutputFactory $factory) {
		$this->outputFactory = $factory;
		$this->logger = new NullLogger();
	}
	
	/**
	 * @param stirng $uuid
	 */
	public function register($uuid) {
		if (!$this->has($uuid)) {
			$this->outputList[$uuid] = $this->outputFactory->create($uuid);
		}
	}
	
	/**
	 * @param string $uuid
	 */
	public function unregister($uuid) {
		if ($output = $this->get($uuid)) {
			$output->close();
			unset($this->outputList[$uuid]);
		}
	}
	
	/**
	 * @param string $uuid
	 * @param Command $command
	 * @return CommandReply
	 */
	public function sendCommand($uuid, Command $command) {
		if ($output = $this->get($uuid)) {
			try {
				$output->sendCommand($command);
				$timeoutTime = TimeProvider::get()->now() + $this->getCommandReplyTimeout();
				while (true) {
					if ($reply = $output->getCommandReply()) {
						return CommandReply::createFromJson($reply);
					}
					
					if (TimeProvider::get()->now() >= $timeoutTime) {
						return CommandReply::error('reply timeout');
					}
				}
			} catch (\Exception $e) {
				$this->logger->error($e);
				return CommandReply::error($e->getMessage());
			}
		}
		
		return CommandReply::error('worker not exists', $uuid);
	}
	
	/**
	 * @param string $uuid
	 * @return null|CommandOutput
	 */
	protected function get($uuid) {
		return $this->has($uuid) ? $this->outputList[$uuid] : null;
	}
	
	/**
	 * @param string $uuid
	 * @return bool
	 */
	public function has($uuid) {
		return isset($this->outputList[$uuid]);
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * @param int $timeout
	 */
	public function setCommandReplyTimeout($timeout) {
		$this->commandReplyTimeout = $timeout;
	}
	
	/**
	 * @return int
	 */
	public function getCommandReplyTimeout() {
		return $this->commandReplyTimeout;
	}
}