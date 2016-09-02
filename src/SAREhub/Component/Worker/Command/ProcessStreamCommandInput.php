<?php

namespace SAREhub\Component\Worker\Command;

class ProcessStreamCommandInput implements CommandInput {
	
	const DEFAULT_REPLY_FORMAT = '###%s###';
	
	protected $inStream;
	protected $outStream;
	protected $deserializer;
	protected $replyFormat;
	
	public function __construct($inStream, $outStream) {
		$this->inStream = $inStream;
		$this->outStream = $outStream;
		$this->replyFormat = self::DEFAULT_REPLY_FORMAT;
	}
	
	/**
	 * @return ProcessStreamCommandInput
	 */
	public function getForStdIO() {
		return new self(STDIN, STDOUT);
	}
	
	/**
	 * @param callable $deserializer
	 * @return $this
	 */
	public function deserializer(callable $deserializer) {
		$this->deserializer = $deserializer;
		return $this;
	}
	
	/**
	 * @param string $replyFormat
	 * @return $this
	 */
	public function replyFormat($replyFormat) {
		$this->replyFormat = $replyFormat;
		return $this;
	}
	
	public function getNextCommand() {
		$command = trim(fgets($this->inStream));
		return empty($command) ? null : $this->deserializeCommand($command);
	}
	
	protected function deserializeCommand($command) {
		$deserializer = $this->deserializer;
		return $deserializer($command);
	}
	
	public function sendCommandReply($reply) {
		fwrite($this->outStream, sprintf($this->replyFormat, $reply));
		return $this;
	}
}

