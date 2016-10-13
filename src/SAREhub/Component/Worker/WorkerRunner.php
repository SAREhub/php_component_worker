<?php

namespace SAREhub\Component\Worker;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandInput;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandReplyOutput;

/**
 * Helper class for run worker.
 */
class WorkerRunner implements LoggerAwareInterface {
	
	private $logger;
	
	/**
	 * @var Worker
	 * */
	private $worker;
	
	/**
	 * @var CommandInput
	 */
	private $commandInput;
	
	private $commandReplyOutput;
	
	protected function __construct() {
		$this->logger = new NullLogger();
	}
	
	/**
	 * @return WorkerRunner
	 */
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
	 * @param CommandInput $input
	 * @return $this
	 */
	public function withCommandInput(CommandInput $input) {
		$this->commandInput = $input;
		return $this;
	}
	
	/**
	 * @param CommandReplyOutput $output
	 * @return $this
	 */
	public function withCommandReplyOutput(CommandReplyOutput $output) {
		$this->commandReplyOutput = $output;
		return $this;
	}
	
	/**
	 * Starts worker
	 */
	public function start() {
		try {
			$this->getWorker()->start();
		} catch (\Exception $e) {
			$this->getLogger()->error($e);
		}
	}
	
	/**
	 * @return bool
	 */
	public function tick() {
		try {
			$this->checkCommand();
			$this->getWorker()->tick();
		} catch (\Exception $e) {
			$this->getLogger()->error($e);
		}
	}
	
	public function stop() {
		try {
			$this->getWorker()->stop();
			$this->getCommandInput()->close();
			$this->getCommandReplyOutput()->close();
		} catch (\Exception $e) {
			$this->getLogger()->error($e);
		}
	}
	
	private function checkCommand() {
		if ($command = $this->getCommandInput()->getNext()) {
			$reply = $this->processCommand($command);
			$this->getLogger()->info('sending reply', ['reply' => $reply]);
			$this->getCommandReplyOutput()->send($reply, true);
		}
	}
	
	
	private function processCommand(Command $command) {
		$this->getLogger()->info('process command', ['command' => (string)$command]);
		try {
			$reply = $this->onCommand($command);
			if ($reply === null) {
				throw new \LogicException('empty reply');
			}
		} catch (\Exception $e) {
			$reply = $this->onProcessCommandException($command, $e);
		}
		
		return $reply;
	}
	
	private function onCommand(Command $command) {
		switch ($command->getName()) {
			case WorkerCommands::STOP:
				return $this->onStopCommand($command);
				break;
			default:
				return $this->worker->processCommand($command);
		}
	}
	
	private function onStopCommand(Command $command) {
		$this->getWorker()->stop();
		return CommandReply::success($command->getCorrelationId(), 'stopped');
	}
	
	private function onProcessCommandException(Command $command, \Exception $e) {
		$this->getLogger()->error($e);
		return CommandReply::error(
		  $command->getCorrelationId(),
		  'exception when execute command', [
		  'exceptionMessage' => $e->getMessage()
		]);
	}
	
	/**
	 * @return bool
	 */
	public function isRunning() {
		return !$this->getWorker()->isStopped();
	}
	
	/**
	 * @return Worker
	 */
	public function getWorker() {
		return $this->worker;
	}
	
	/**
	 * @return CommandInput
	 */
	public function getCommandInput() {
		return $this->commandInput;
	}
	
	/**
	 * @return CommandReplyOutput
	 */
	public function getCommandReplyOutput() {
		return $this->commandReplyOutput;
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