<?php
namespace SAREhub\Component\Worker\Command;

interface CommandOutputFactory {
	
	/**
	 * @param string $workerId
	 * @return CommandOutput
	 */
	public function create($workerId);
}