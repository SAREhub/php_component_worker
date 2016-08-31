<?php

namespace SAREhub\Component\Worker\Command;

use Symfony\Component\Process\Process;

class ProcessStreamCommandOutput implements CommandOutput {
	
	/** @var \resource */
	protected $processInputStream;
	
	/** @var Process */
	protected $process;
	
	/** @var string */
	protected $processOutput = '';
	
	/** @var string */
	private $replyPattern;
	
	/**@var callable */
	private $serializer;
	
	/**
	 * @param Process $process
	 * @param string $replyPattern Regex pattern for find command reply in process stdout
	 * @param callable $serializer
	 */
	public function __construct(Process $process, callable $serializer, $replyPattern = '/###(.+)###/') {
		$this->processInputStream = $process->getInput();
		$this->process = $process;
		$this->replyPattern = $replyPattern;
		$this->serializer = $serializer;
	}
	
	public function sendCommand(Command $command) {
		fwrite($this->processInputStream, $this->serializeCommand($command)."\n");
	}
	
	/**
	 * @param Command $command
	 * @return mixed
	 */
	protected function serializeCommand(Command $command) {
		$serializer = $this->serializer;
		return $serializer($command);
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
