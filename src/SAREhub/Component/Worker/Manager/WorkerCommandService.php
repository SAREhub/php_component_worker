<?php

namespace SAREhub\Component\Worker\Manager;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\CommandOutputFactory;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Service\ServiceSupport;

class WorkerCommandService extends ServiceSupport {
	
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
	
	public function __construct(CommandOutputFactory $factory) {
		$this->outputFactory = $factory;
		$this->logger = new NullLogger();
	}
	
	/**
	 * @param string $id
	 */
	public function register($id) {
		if (!$this->has($id)) {
			$this->outputList[$id] = $this->outputFactory->create($id);
		}
	}
	
	/**
	 * @param string $id
	 */
	public function unregister($id) {
		if ($output = $this->get($id)) {
			$output->close();
			unset($this->outputList[$id]);
		}
	}
	
	/**
	 * @param string $id
	 * @param Command $command
	 * @param int $replyTimeout
	 * @return CommandReply
	 */
	public function sendCommand($id, Command $command, $replyTimeout = self::DEFAULT_COMMAND_REPLY_TIMEOUT) {
		if ($output = $this->get($id)) {
			try {
				$output->sendCommand($command);
				$timeoutTime = TimeProvider::get()->now() + $replyTimeout;
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
		
		return CommandReply::error('worker not exists', $id);
	}
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
		
	}
	
	protected function doStop() {
		foreach ($this->outputList as $id => $output) {
			$this->unregister($id);
		}
	}
	
	
	/**
	 * @param string $id
	 * @return null|CommandOutput
	 */
	protected function get($id) {
		return $this->has($id) ? $this->outputList[$id] : null;
	}
	
	/**
	 * @param string $id
	 * @return bool
	 */
	public function has($id) {
		return isset($this->outputList[$id]);
	}
	
	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}
}