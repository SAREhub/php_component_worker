<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Manager\WorkerCommandRequest;

class WorkerCommandRequestTest extends TestCase {
	
	public function testIsSentThenReturnFalse() {
		$this->assertFalse(WorkerCommandRequest::newInstance()->isSent());
	}
	
	public function testMarkAsSentThenIsSentReturnTrue() {
		$request = WorkerCommandRequest::newInstance();
		$request->markAsSent(time());
		$this->assertTrue($request->isSent());
	}
	
	public function testIsReplyTimeoutWhenNotSentThenReturnFalse() {
		$request = WorkerCommandRequest::newInstance();
		$this->assertFalse($request->isReplyTimeout(time()));
	}
	
	public function testIsReplyTimeoutWhenSentAndNotTimeoutThenReturnFalse() {
		$request = WorkerCommandRequest::newInstance();
		$now = time();
		$request->markAsSent($now);
		$this->assertFalse($request->isReplyTimeout($now));
	}
}
