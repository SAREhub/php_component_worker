<?php

namespace SAREhub\Component\Worker\Command;

class ProcessStreamCommandInput implements CommandInput {
	
	/** @var \resource */
	protected $inStream;
	
	/** @var \resource */
	protected $outStream;
	
	/** @var string */
	private $replyFormat;
	
	/** @var callable */
	private $deserializer;
	
	public function __construct($inStream, $outStream, callable $deserializer, $replyFormat = '###%s###') {
		$this->inStream = $inStream;
		$this->outStream = $outStream;
		$this->replyFormat = $replyFormat;
		$this->deserializer = $deserializer;
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
	}
}