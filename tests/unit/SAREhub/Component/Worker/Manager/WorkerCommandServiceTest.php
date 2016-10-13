<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandReplyInput;
use SAREhub\Component\Worker\Manager\WorkerCommandRequest;
use SAREhub\Component\Worker\Manager\WorkerCommandService;

class WorkerCommandServiceTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $outputMock;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $inputMock;
	
	/**
	 * @var WorkerCommandService
	 */
	private $service;
	
	/**
	 * @var WorkerCommandRequest
	 */
	private $request;
	
	protected function setUp() {
		parent::setUp();
		$this->outputMock = $this->createMock(CommandOutput::class);
		$this->inputMock = $this->createMock(CommandReplyInput::class);
		$this->service = WorkerCommandService::newInstance()
		  ->withCommandOutput($this->outputMock)
		  ->withCommandReplyInput($this->inputMock);
		
		$this->service->start();
		
		$this->request = WorkerCommandRequest::newInstance()
		  ->withWorkerId('worker1')
		  ->withCommand(new BasicCommand('1', 'c'))
		  ->withReplyCallback($this->createPartialMock(stdClass::class, ['__invoke']));
	}
	
	protected function tearDown() {
		parent::tearDown();
		TimeProvider::get()->unfreezeTime();
	}
	
	public function testProcessThenOutputSend() {
		$this->outputMock->expects($this->once())->method('send')
		  ->with(
			$this->request->getWorkerId(),
			$this->request->getCommand(),
			false
		  );
		$this->service->process($this->request);
	}
	
	public function testProcessThenRequestIsSent() {
		$this->service->process($this->request);
		$this->assertTrue($this->request->isSent());
	}
	
	public function testProcessThenRequestSentTimeIsNow() {
		TimeProvider::get()->freezeTime();
		$this->service->process($this->request);
		$this->assertEquals(TimeProvider::get()->now(), $this->request->getSentTime());
	}
	
	public function testProcessThenPendingRequest() {
		$this->service->process($this->request);
		$this->assertEquals([$this->request], $this->service->getPendingRequests());
	}
	
	public function testProcessWhenSendExceptionThenCallReplyCallbackWithErrorReply() {
		$this->outputMock->method('send')->willThrowException(new \Exception('m'));
		$this->request->getReplyCallback()->expects($this->once())->method('__invoke')
		  ->with($this->request, $this->callback(function (CommandReply $reply) {
			  return $reply->isError();
		  }));
		$this->service->process($this->request);
	}
	
	public function testProcessWhenSendExceptionThenNotPendingRequest() {
		$this->outputMock->method('send')->willThrowException(new \Exception('m'));
		$this->service->process($this->request);
		$this->assertEmpty($this->service->getPendingRequests());
	}
	
	public function testProcessWhenSendExceptionThenNotSent() {
		$this->outputMock->method('send')->willThrowException(new \Exception('m'));
		$this->service->process($this->request);
		$this->assertFalse($this->request->isSent());
	}
	
	public function testDoTickWhenReplyThenCallReplyCallback() {
		$this->service->process($this->request);
		$reply = CommandReply::success(
		  $this->request->getCommand()->getCorrelationId(),
		  'm'
		);
		
		$this->inputMock->method('getNext')->willReturn($reply);
		$this->request->getReplyCallback()->expects($this->once())->method('__invoke')
		  ->with($this->request, $reply);
		$this->service->tick();
	}
	
	public function testDoTickWhenReplyNotCorrelatedThenIgnore() {
		$reply = CommandReply::success(
		  $this->request->getCommand()->getCorrelationId(),
		  'm'
		);
		
		$this->inputMock->method('getNext')->willReturn($reply);
		$this->request->getReplyCallback()->expects($this->never())->method('__invoke');
		$this->service->tick();
	}
	
	public function testDoTickWhenRequestReplyTimeoutThenCallReplyCallbackWithErrorReply() {
		$now = time();
		TimeProvider::get()->freezeTime($now);
		$this->service->process($this->request);
		TimeProvider::get()->freezeTime($now + $this->request->getReplyTimeout());
		
		$this->request->getReplyCallback()->expects($this->once())->method('__invoke')
		  ->with($this->request, $this->callback(function (CommandReply $reply) {
			  return $reply->isError();
		  }));
		$this->service->tick();
	}
}
