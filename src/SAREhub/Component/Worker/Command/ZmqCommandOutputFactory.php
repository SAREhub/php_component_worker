<?php

namespace SAREhub\Component\Worker\Command;

use SAREhub\Commons\Zmq\RequestReply\RequestSender;

class ZmqCommandOutputFactory implements CommandOutputFactory {
	
	private $format;
	private $zmqContext;
	
	/**
	 * @var callable
	 */
	private $dsnFactory;
	
	public function __construct(CommandFormat $format, \ZMQContext $context, callable $dsnFactory) {
		$this->format = $format;
		$this->zmqContext = $context;
		$this->dsnFactory = $dsnFactory;
	}
	
	public function create($workerUuid) {
		$sender = RequestSender::inContext($this->zmqContext)
		  ->connect(($this->dsnFactory)($workerUuid));
		return new ZmqCommandOutput($sender, $this->format);
	}
}