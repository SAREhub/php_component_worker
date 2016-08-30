<?php

namespace SAREhub\Component\Worker\Command;

class ProcessStreamWorkerCommandInput implements WorkerCommandInput {
	
	/** @var \resource */
	protected $inStream;
	
	/** @var \resource */
	protected $outStream;
	
	/** @var string */
	protected $confirmationString;
	
	public function __construct($inStream, $outStream, $confirmationString = "1\n") {
		$this->inStream = $inStream;
		$this->outStream = $outStream;
		$this->confirmationString = $confirmationString;
	}
	
	public function getNextCommand() {
		$command = trim(fgets($this->inStream));
		if (!empty($command)) {
			return WorkerCommand::fromJson($command);
		}
		
		return null;
	}
	
	public function sendCommandConfirmation() {
		fwrite($this->outStream, $this->confirmationString);
	}
}