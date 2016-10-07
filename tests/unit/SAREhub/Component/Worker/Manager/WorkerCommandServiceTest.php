<?php

use Guzzle\Service\Exception\CommandException;
use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\TimeProvider;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandOutput;
use SAREhub\Component\Worker\Command\CommandOutputFactory;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Manager\WorkerCommandService;

class WorkerCommandServiceTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $factoryMock;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $outputMock;
	
	/**
	 * @var WorkerCommandService
	 */
	private $service;
	
	protected function setUp() {
		parent::setUp();
		$this->factoryMock = $this->createMock(CommandOutputFactory::class);
		$this->outputMock = $this->createMock(CommandOutput::class);
		$this->factoryMock->method('create')->willReturn($this->outputMock);
		$this->service = new WorkerCommandService($this->factoryMock);
	}
	
	public function testRegisterWhenNotExists() {
		$this->factoryMock->expects($this->once())->method('create')
		  ->with('id')->willReturn($this->outputMock);
		$this->service->register('id');
		$this->assertTrue($this->service->has('id'));
	}
	
	public function testRegisterWhenExists() {
		$this->service->register('id');
		$this->factoryMock->expects($this->never())->method('create');
		$this->service->register('id');
		$this->assertTrue($this->service->has('id'));
	}
	
	public function testUnregisterWhenExists() {
		$this->service->register('id');
		$this->outputMock->expects($this->once())->method('close');
		$this->service->unregister('id');
		$this->assertFalse($this->service->has('id'));
	}
	
	public function testHasWhenNotExists() {
		$this->assertFalse($this->service->has('not_exists'));
	}
	
	public function testSendCommandWhenExists() {
		$this->service->register('id');
		$command = new BasicCommand('c');
		$this->outputMock->expects($this->once())->method('sendCommand')
		  ->with($this->identicalTo($command));
		$this->outputMock->expects($this->once())->method('getCommandReply')
		  ->willReturn(json_encode(CommandReply::success('m')));
		
		$reply = $this->service->sendCommand('id', $command);
		$this->assertEquals('m', $reply->getMessage());
	}
	
	public function testSendCommandWhenNotExists() {
		$command = new BasicCommand('c');
		$this->outputMock->expects($this->never())->method('sendCommand');
		$this->outputMock->expects($this->never())->method('getCommandReply');
		$reply = $this->service->sendCommand('id', $command);
		$this->assertEquals('worker not exists', $reply->getMessage());
	}
	
	public function testSendCommandWhenReplyTimeout() {
		$this->service->register('id');
		$command = new BasicCommand('c');
		$this->outputMock->expects($this->once())->method('getCommandReply');
		TimeProvider::get()->freezeTime();
		$this->service->setCommandReplyTimeout(0);
		$reply = $this->service->sendCommand('id', $command);
		$this->assertEquals('reply timeout', $reply->getMessage());
		TimeProvider::get()->unfreezeTime();
	}
	
	public function testSendCommandWhenSendCommandException() {
		$this->service->register('id');
		$command = new BasicCommand('c');
		$this->outputMock->method('sendCommand')
		  ->willThrowException(new CommandException('m'));
		$reply = $this->service->sendCommand('id', $command);
		$this->assertEquals('m', $reply->getMessage());
	}
	
	public function testSendCommandWhenGetCommandReplyException() {
		$this->service->register('id');
		$command = new BasicCommand('c');
		$this->outputMock->method('sendCommand')
		  ->willThrowException(new CommandException('m'));
		$reply = $this->service->sendCommand('id', $command);
		$this->assertEquals('m', $reply->getMessage());
	}
}
