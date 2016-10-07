<?php

namespace SAREhub\Component\Worker\Command;


class StandardCommandEnum {
	
	const STOP = 'worker.stop';
	const RESTART = 'worker.restart';
	
	const PAUSE = 'worker.pause';
	const RESUME = 'worker.resume';
	
	const STATUS = 'worker.status';
	const INFO = 'worker.info';
}