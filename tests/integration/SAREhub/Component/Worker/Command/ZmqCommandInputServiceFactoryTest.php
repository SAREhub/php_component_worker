<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\Dsn;
use SAREhub\Component\Worker\Command\JsonCommandFormat;
use SAREhub\Component\Worker\Command\ZmqCommandInputServiceFactory;

class ZmqCommandInputServiceFactoryTest extends TestCase {
	
	/**
	 * @var ZmqCommandInputServiceFactory
	 */
	private $factory;
	
	protected function setUp() {
		$zmqContext = $this->createMock(ZMQContext::class);
		$socket = $this->createMock(ZMQSocket::class);
		$zmqContext->method('getSocket')->willReturn($socket);
		$this->factory = ZmqCommandInputServiceFactory::newInstance()
		  ->withZmqContext($zmqContext)
		  ->withCommandInputTopic('input_topic')
		  ->withEndpointPrefix('prefix');
	}
	
	public function testCreateCommandInputThenSubscriberTopics() {
		$input = $this->factory->createCommandInput();
		$this->assertEquals(['input_topic'], $input->getSubscriber()->getTopics());
	}
	
	public function testCreateCommandInputThenSubscriberConnections() {
		$input = $this->factory->createCommandInput();
		$expectedConnection = Dsn::ipc()->endpoint('prefix/workerCommandInput.sock');
		$expectedConnections = [(string)$expectedConnection => $expectedConnection];
		$this->assertEquals($expectedConnections, $input->getSubscriber()->getConnections());
	}
	
	public function testCreateCommandInputThenCommandFormat() {
		$input = $this->factory->createCommandInput();
		$this->assertInstanceOf(JsonCommandFormat::class, $input->getCommandFormat());
	}
	
	public function testCreateCommandReplyOutputThenPublisherConnections() {
		$output = $this->factory->createCommandReplyOutput();
		$expectedConnection = Dsn::ipc()->endpoint('prefix/workerCommandReplyOutput.sock');
		$expectedConnections = [(string)$expectedConnection => $expectedConnection];
		$this->assertEquals($expectedConnections, $output->getPublisher()->getConnections());
	}
	
	public function testCreateCommandReplyOutputThenPublisherPublishTopic() {
		$output = $this->factory->createCommandReplyOutput();
		$expected = ZmqCommandInputServiceFactory::DEFAULT_COMMAND_REPLY_OUTPUT_PUBLISH_TOPIC;
		$this->assertEquals($expected, $output->getPublishTopic());
	}
}

