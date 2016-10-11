<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\RequestReply\RequestSender;
use SAREhub\Component\Worker\Command\CommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandOutput;
use SAREhub\Component\Worker\Command\ZmqCommandOutputFactory;

class ZmqCommandOutputFactoryTest extends TestCase {
	
	private $commandFormatMock;
	private $senderMock;
	private $senderFactoryMock;
	
	private $factory;
	
	protected function setUp() {
		parent::setUp();
		$this->commandFormatMock = $this->createMock(CommandFormat::class);
		$this->senderMock = $this->createMock(RequestSender::class);
		$this->senderFactoryMock = $this->getMockBuilder(stdClass::class)->setMethods(['__invoke'])->getMock();
		$this->senderFactoryMock->method('__invoke')->willReturn($this->senderMock);
		$this->factory = new ZmqCommandOutputFactory($this->senderFactoryMock, $this->commandFormatMock);
	}
	
	public function testCreateThenCreateCallSenderFactory() {
		$workerId = 'worker1';
		$this->senderFactoryMock->expects($this->once())->method('__invoke')
		  ->with($workerId)->willReturn($this->senderMock);
		$this->factory->create($workerId);
	}
	
	public function testCreateThenReturnOutput() {
		$this->assertInstanceOf(ZmqCommandOutput::class, $this->factory->create('workerId'));
	}
	
	public function testCreateThenSenderSets() {
		$output = $this->factory->create('worker1');
		$this->assertSame($this->senderMock, $output->getSender());
	}
	
	public function testCreateThenCommandFormatSets() {
		$output = $this->factory->create('worker1');
		$this->assertSame($this->commandFormatMock, $output->getCommandFormat());
	}
}
