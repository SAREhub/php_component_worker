<?php

namespace SAREhub\Component\Worker\Command\Standard;


use SAREhub\Component\Worker\Command\WorkerManagerCommand;

class KillAllWorkerManagerCommand implements WorkerManagerCommand {
	
	const NAME = 'command.manager.worker.all.kill';
	
	public function getName() {
		return self::NAME;
	}
}