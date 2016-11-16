<?php

namespace SAREhub\Component\Worker\Command;

use Respect\Validation\Validator;
use SAREhub\Commons\Misc\Dsn;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;

class ZmqCommandInputServiceFactory implements CommandInputServiceFactory {
	
	const DEFAULT_COMMAND_REPLY_OUTPUT_PUBLISH_TOPIC = 'worker.command.reply';
	
	private $endpointPrefix;
	private $commandInputTopic;
	private $commandReplyOutputTopic = self::DEFAULT_COMMAND_REPLY_OUTPUT_PUBLISH_TOPIC;
	private $zmqContext;
	
	/**
	 * @return ZmqCommandInputServiceFactory
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param \ZMQContext $context
	 * @return $this
	 */
	public function withZmqContext(\ZMQContext $context) {
		$this->zmqContext = $context;
		return $this;
	}
	
	/**
	 * @param string $prefix
	 * @return $this
	 */
	public function withEndpointPrefix($prefix) {
		$this->endpointPrefix = $prefix;
		return $this;
	}
	
	/**
	 * @param string $topic
	 * @return $this
	 */
	public function withCommandInputTopic($topic) {
		$this->commandInputTopic = $topic;
		return $this;
	}
	
	/**
	 * @return ZmqCommandInput
	 */
	public function createCommandInput() {
		$this->checkSetup();
		return ZmqCommandInput::newInstance()
		  ->withCommandSubscriber($this->createSubscriber())
		  ->withCommandFormat(JsonCommandFormat::newInstance());
	}
	
	/**
	 * @return ZmqCommandReplyOutput
	 */
	public function createCommandReplyOutput() {
		$this->checkSetup();
		return ZmqCommandReplyOutput::newInstance()
		  ->withPublisher($this->createPublisher())
		  ->withPublishTopic($this->commandReplyOutputTopic);
	}
	
	/**
	 * @return Subscriber
	 */
	private function createSubscriber() {
		return Subscriber::inContext($this->zmqContext)
		  ->subscribe($this->commandInputTopic)
		  ->connect($this->createDsn('workerCommandInput'));
	}
	
	/**
	 * @return Publisher
	 */
	private function createPublisher() {
		return Publisher::inContext($this->zmqContext)
		  ->connect($this->createDsn('workerCommandReplyOutput'));
	}
	
	private function checkSetup() {
		$v = Validator::notEmpty();
		$v->setName('zmqContext')->assert($this->zmqContext);
		$v->setName('endpointPrefix')->assert($this->endpointPrefix);
		$v->setName('commandInputTopic')->assert($this->commandInputTopic);
	}
	
	private function createDsn($name) {
		return Dsn::ipc()->endpoint($this->endpointPrefix.'/'.$name.'.sock');
	}
}