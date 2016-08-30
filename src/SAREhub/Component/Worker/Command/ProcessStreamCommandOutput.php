<?php

namespace SAREhub\Component\Worker\Command;

use Symfony\Component\Process\Process;

class ProcessStreamCommandOutput implements WorkerCommandOutput {
	
	/** @var \resource */
	protected $processInputStream;
	
	/** @var Process */
	protected $process;
	
	/** @var string */
	protected $confirmationPattern;
	
	/** @var string */
	protected $processOutput = '';
	
	public function __construct(Process $process, $confirmationPattern = "1\n") {
		$this->processInputStream = $process->getInput();
		$this->process = $process;
		$this->confirmationPattern = $confirmationPattern;
	}
	
	public function sendCommand(WorkerCommand $command) {
		fwrite($this->processInputStream, json_encode(($command))."\n");
	}
	
	public function getCommandConfirmation() {
		$this->processOutput .= $this->process->getIncrementalOutput();
		if (!empty($this->processOutput)) {
			$confirmationPos = strpos($this->processOutput, $this->confirmationPattern);
			if ($confirmationPos !== false) {
				$this->processOutput = substr($this->processOutput, $confirmationPos);
				return true;
			}
		}
		
		return false;
	}
}