<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\CommandRequest;

class CommandRequestTest extends TestCase {
	
	private $request;
	
	protected function setUp() {
		parent::setUp();
		$this->request = CommandRequest::newInstance();
	}
	
	public function testIsSentThenReturnFalse() {
		$this->assertFalse($this->request->isSent());
	}
	
	public function testMarkAsSentThenIsSentReturnTrue() {
		$this->request->markAsSent(time());
		$this->assertTrue($this->request->isSent());
	}
	
	public function testIsReplyTimeoutWhenNotSentThenReturnFalse() {
		$this->assertFalse($this->request->isReplyTimeout(time()));
	}
	
	public function testIsReplyTimeoutWhenSentAndNotTimeoutThenReturnFalse() {
		$now = time();
		$this->request->markAsSent($now);
		$this->assertFalse($this->request->isReplyTimeout($now));
	}
}
