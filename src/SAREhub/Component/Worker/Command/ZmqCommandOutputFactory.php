<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutputFactory implements CommandOutputFactory {
	
	private $senderFactory;
	private $format;
	
	/**
	 * @param callable $senderFactory
	 * @param CommandFormat $format
	 */
	public function __construct(callable $senderFactory, CommandFormat $format) {
		$this->senderFactory = $senderFactory;
		$this->format = $format;
	}
	
	public function create($workerId) {
		return new ZmqCommandOutput(($this->senderFactory)($workerId), $this->format);
	}
	
	
	/**
	 * @param \ZMQContext $context
	 * @param callable $dsnFactory
	 * @return \Closure
	 */
	public static function getSenderFactory(\ZMQContext $context, callable $dsnFactory) {
		return function ($workerId) use ($context, $dsnFactory) {
			return RequestSender::inContext($context)->connect(($dsnFactory)($workerId));
		};
	}
}