<?php

namespace SAREhub\Component\Worker\Logger;

use PHPUnit\Framework\TestCase;

class WorkerIdProcessorTest extends TestCase {
	
	public function testInvokeThenExtraHasWorkerId() {
		$workerId = 'worker1';
		$p = new WorkerIdProcessor($workerId);
		$record = ['extra' => []];
		$this->assertEquals(['extra' => ['workerId' => $workerId]], $p($record));
	}
}
