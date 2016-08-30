<?php

namespace SAREhub\Component\Worker\Command;


interface WorkerCommandOutput {
	
	/**
	 * @param WorkerCommand $command
	 * @return mixed
	 */
	public function sendCommand(WorkerCommand $command);
	
	public function getCommandConfirmation();
}