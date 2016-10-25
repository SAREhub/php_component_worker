<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Service\ServiceSupport;

class CommandService extends ServiceSupport {
	
	/**
	 * @var CommandOutput
	 */
	private $commandOutput;
	
	/**
	 * @var CommandReplyInput
	 */
	private $commandReplyInput;
	
	/**
	 * @var CommandRequest[]
	 */
	private $pendingRequests = [];
	
	
	protected function __construct() {
	}
	
	/**
	 * @return CommandService
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
	
	public function process(CommandRequest $request) {
		$this->getLogger()->info('sending command request', ['request' => $request]);
		try {
			$this->commandOutput->send($request->getTopic(), $request->getCommand(), false);
			$request->markAsSent(TimeProvider::get()->now());
			
			$correlationId = $request->getCommand()->getCorrelationId();
			$this->pendingRequests[$correlationId] = $request;
			if (!$request->isAsync()) {
				while (isset($this->pendingRequests[$correlationId])) {
					$this->doTick();
				}
			}
			
		} catch (\Exception $e) {
			$this->onRequestException($request, $e);
		}
	}
	
	
	
	protected function doStart() {
		
	}
	
	protected function doTick() {
		if ($reply = $this->getCommandReplyInput()->getNext()) {
			$this->getLogger()->info('got reply', ['reply' => $reply]);
			if ($request = $this->getCorrelatedPendingRequest($reply)) {
				$this->getLogger()->info('exists correlated command', [
				  'request' => $request,
				  'reply' => $reply
				]);
				($request->getReplyCallback())($request, $reply);
				$this->removeRequest($request->getCommand()->getCorrelationId());
			}
			$this->getLogger()->info('not exists correlated command for reply', ['reply' => $reply]);
		}
		
		$this->checkReplyTimeout();
	}
	
	protected function doStop() {
		$this->getCommandOutput()->close();
		$this->getCommandReplyInput()->close();
	}
	
	private function onRequestException(CommandRequest $request, \Exception $exception) {
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
	 * @return CommandRequest|null
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
				$this->removeRequest($request->getCommand()->getCorrelationId());
			}
		}
	}
	
	private function removeRequest($correlationId) {
		unset($this->pendingRequests[$correlationId]);
	}
	
	/**
	 * @return CommandRequest[]
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
}