<?php

namespace SAREhub\Component\Worker\Command;

use Symfony\Component\Process\Process;

/**
 * Implementation of CommandOutput interface
 * Command will be send to selected process stdin
 */
class ProcessStreamCommandOutput implements CommandOutput {
	
	const DEFAULT_REPLY_PATTERN = '/###(.+)###/';
	
	protected $process;
	protected $replyPattern = self::DEFAULT_REPLY_PATTERN;
	
	protected $serializer;
	protected $processInputStream;
	protected $processOutput = '';
	
	/**
	 * ProcessStreamCommandOutput constructor.
	 * @param Process $process
	 */
	public function __construct(Process $process) {
		$this->process = $process;
		$this->processInputStream = $process->getInput();
		$this->serializer = self::getDefaultSerializer();
	}
	
	/**
	 * @param Process $process
	 * @return ProcessStreamCommandOutput
	 */
	public static function getForProcess(Process $process) {
		return new self($process);
	}
	
	/**
	 * Returns default implementation of command serializer(using Command::__toString method).
	 * @return \Closure
	 */
	public static function getDefaultSerializer() {
		return function (Command $command) {
			return (string)$command;
		};
	}
	
	/**
	 * Function for serialize command to send.
	 * @param callable $serializer
	 * @return $this
	 */
	public function serializer(callable $serializer) {
		$this->serializer = $serializer;
		return $this;
	}
	
	/**
	 * Regex pattern for find command reply in process output.
	 * @param string $replyPattern
	 * @return $this
	 */
	public function replyPattern($replyPattern) {
		$this->replyPattern = $replyPattern;
		return $this;
	}
	
	public function sendCommand(Command $command) {
		$serializer = $this->serializer;
		fwrite($this->processInputStream, $serializer($command)."\n");
		return $this;
	}
	
	public function getCommandReply() {
		$this->processOutput .= $this->process->getIncrementalOutput();
		if (!empty($this->processOutput)) {
			if ($reply = $this->findReplyPattern()) {
				$this->processOutput = substr($this->processOutput, $reply['offset'] + 1);
				return $reply['content'];
			}
		}
		
		return null;
	}
	
	protected function findReplyPattern() {
		$matches = [];
		if (preg_match($this->replyPattern, $this->processOutput, $matches, PREG_OFFSET_CAPTURE)) {
			return [
			  'content' => $matches[1][0],
			  'offset' => $matches[1][1]
			];
		}
		
		return null;
	}
}
