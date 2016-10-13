<?php

namespace SAREhub\Component\Worker\Manager;

use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandReplyInput;
use SAREhub\Component\Worker\Service\ServiceSupport;

class WorkerCommandService extends ServiceSupport {
	
	/**
	 * @var CommandOutput
	 */
	private $commandOutput;
	
	/**
	 * @var CommandReplyInput
	 */
	private $commandReplyInput;
	
	/**
	 * @var WorkerCommandRequest[]
	 */
	private $pendingRequests = [];
	
	
	protected function __construct() {
	}
	
	/**
	 * @return WorkerCommandService
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param CommandOutput $output
	 * @return $this
	 */
	public function withCommandOutput(CommandOutput $output) {
		$this->commandOutput = $output;
		return $this;
	}
	
	/**
	 * @param CommandReplyInput $input
	 * @return $this
	 */
	public function withCommandReplyInput(CommandReplyInput $input) {
		$this->commandReplyInput = $input;
		return $this;
	}
	
	public function process(WorkerCommandRequest $request) {
		$this->getLogger()->info('sending command request', ['request' => $request]);
		try {
			$this->commandOutput->send($request->getWorkerId(), $request->getCommand(), false);
			$request->markAsSent(TimeProvider::get()->now());
			$this->pendingRequests[] = $request;
		} catch (\Exception $e) {
			$this->onRequestException($request, $e);
		}
	}
	
	/**
	 * @return WorkerCommandRequest[]
	 */
	public function getPendingRequests() {
		return $this->pendingRequests;
	}
	
	/**
	 * @return CommandOutput
	 */
	public function getCommandOutput() {
		return $this->commandOutput;
	}
	
	/**
	 * @return CommandReplyInput
	 */
	public function getCommandReplyInput() {
		return $this->commandReplyInput;
	}
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
		if ($reply = $this->getCommandReplyInput()->getNext()) {
			$this->getLogger()->info('got reply', ['reply' => $reply]);
			if ($request = $this->getCorrelatedPendingRequest($reply)) {
				$this->getLogger()->info('exists correlated command',
				  ['request' => $request],
				  ['reply' => $reply]
				);
				($request->getReplyCallback())($request, $reply);
			}
			$this->getLogger()->info('not exists correlated command for reply', ['reply' => $reply]);
		}
		
		$this->checkReplyTimeout();
	}
	
	protected function doStop() {
	}
	
	private function onRequestException(WorkerCommandRequest $request, \Exception $exception) {
		$this->getLogger()->error($exception, ['request' => $request]);
		$reply = CommandReply::error(
		  $request->getCommand()->getCorrelationId(),
		  'exception when send command',
		  ['exceptionMessage' => $exception->getMessage()]
		);
		($request->getReplyCallback())($request, $reply);
	}
	
	/**
	 * @param CommandReply $reply
	 * @return null|WorkerCommandRequest
	 */
	private function getCorrelatedPendingRequest(CommandReply $reply) {
		foreach ($this->getPendingRequests() as $request) {
			if ($reply->getCorrelationId() === $request->getCommand()->getCorrelationId()) {
				return $request;
			}
		}
		
		return null;
	}
	
	private function checkReplyTimeout() {
		foreach ($this->getPendingRequests() as $request) {
			if ($request->isReplyTimeout(TimeProvider::get()->now())) {
				$reply = CommandReply::error(
				  $request->getCommand()->getCorrelationId(),
				  'reply timeout'
				);
				($request->getReplyCallback())($request, $reply);
			}
		}
	}
}