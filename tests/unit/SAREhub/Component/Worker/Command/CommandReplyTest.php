<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\CommandReply;

class CommandReplyTest extends TestCase {
	
	public function testReply() {
		$reply = CommandReply::reply('id', 's', 'm', 'd');
		$this->assertEquals('id', $reply->getCorrelationId());
		$this->assertEquals('s', $reply->getStatus());
		$this->assertEquals('m', $reply->getMessage());
		$this->assertEquals('d', $reply->getData());
	}
	
	public function testSuccess() {
		$reply = CommandReply::success('id', 'm', 'd');
		$this->assertEquals(CommandReply::SUCCESS_STATUS, $reply->getStatus());
		$this->assertEquals('m', $reply->getMessage());
		$this->assertEquals('d', $reply->getData());
	}
	
	public function testError() {
		$reply = CommandReply::error('id', 'm', 'd');
		$this->assertEquals(CommandReply::ERROR_STATUS, $reply->getStatus());
		$this->assertEquals('m', $reply->getMessage());
		$this->assertEquals('d', $reply->getData());
	}
	
	public function testIsSuccess() {
		$reply = CommandReply::success('id', 'm', 'd');
		$this->assertTrue($reply->isSuccess());
	}
	
	public function testIsError() {
		$reply = CommandReply::error('id', 'm', 'd');
		$this->assertTrue($reply->isError());
	}
	
}
