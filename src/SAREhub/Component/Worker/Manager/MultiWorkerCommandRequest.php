<?php

namespace SAREhub\Component\Worker\Manager;


class MultiWorkerCommandRequest extends CommandRequest {
	
	private $workerIdList;
	
	private $command;
	
	private $workerRequests;
}