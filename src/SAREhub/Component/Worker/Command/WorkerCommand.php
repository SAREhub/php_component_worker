<?php

namespace SAREhub\Component\Worker\Command;

interface WorkerCommand extends Command {
	
	/**
	 * @return string
	 */
	public function getUuid();
}