<?php
namespace SAREhub\Component\Worker\Command;

interface CommandOutputFactory {
	
	/**
	 * @param string $workerUuid
	 * @return CommandOutput
	 */
	public function create($workerUuid);
}