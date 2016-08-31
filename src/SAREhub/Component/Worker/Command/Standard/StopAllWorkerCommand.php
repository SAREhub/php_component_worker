<?php

namespace SAREhub\Component\Worker\Command\Standard;

use SAREhub\Component\Worker\Command\WorkerManagerCommand;

class StopAllWorkerManagerCommand implements WorkerManagerCommand {
	
	const NAME = 'command.manager.worker.all.stop';
	
	public function getName() {
		return self::NAME;
	}
}