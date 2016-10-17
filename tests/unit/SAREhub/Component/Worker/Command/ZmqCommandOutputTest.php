<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\CommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandOutput;

class ZmqCommandOutputTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $publisher;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $commandFormat;
	
	/**
	 * @var ZmqCommandOutput
	 */
	private $output;
	
	protected function setUp() {
		parent::setUp();
		$this->publisher = $this->createMock(Publisher::class);
		$this->commandFormat = $this->createMock(CommandFormat::class);
		$this->commandFormat->method('marshal')->willReturn('command_data');
		$this->output = ZmqCommandOutput::newInstance()
		  ->withPublisher($this->publisher)
		  ->withCommandFormat($this->commandFormat);
	}
	
	public function testSendThenPublisherCallPublish() {
		$this->publisher->expects($this->once())->method('publish')->with('topic', 'command_data', false);
		$this->output->send('topic', new BasicCommand('1', 'test'));
	}
	
	public function testSendWhenWaitThenPublisherPublishWait() {
		$this->publisher->expects($this->once())->method('publish')->with('topic', 'command_data', true);
		$this->output->send('topic', new BasicCommand('1', 'test'), true);
	}
	
	public function testCloseThenPublisherCallUnbind() {
		$this->publisher->expects($this->once())->method('close');
		$this->output->close();
	}
	
}
