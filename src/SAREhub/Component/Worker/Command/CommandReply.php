<?php

namespace SAREhub\Component\Worker\Command;

class CommandReply implements \JsonSerializable {
	
	const SUCCESS_STATUS = 'success';
	const ERROR_STATUS = 'error';
	
	private $correlationId;
	
	/**
	 * @var string
	 */
	private $status;
	
	/**
	 * @var string
	 */
	private $message;
	
	/**
	 * @var mixed
	 */
	private $data;
	
	/**
	 * CommandReply constructor.
	 * @param $correlationId
	 * @param $status
	 * @param $message
	 * @param $data
	 */
	protected function __construct($correlationId, $status, $message, $data) {
		$this->correlationId = $correlationId;
		$this->status = $status;
		$this->message = $message;
		$this->data = $data;
	}
	
	
	/**
	 * @param string $reply
	 * @return CommandReply
	 */
	public static function createFromJson($reply) {
		return self::createFromArray(json_decode($reply, true));
	}
	
	/**
	 * @param array $reply
	 * @return CommandReply
	 */
	public static function createFromArray(array $reply) {
		return new self(
		  $reply['correlation_id'],
		  $reply['status'],
		  $reply['message'],
		  $reply['data']
		);
	}
	
	/**
	 * @param $correlationId
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function success($correlationId, $message, $data = null) {
		return self::reply($correlationId, self::SUCCESS_STATUS, $message, $data);
	}
	
	/**
	 * @param $correlationId
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function error($correlationId, $message, $data = null) {
		return self::reply($correlationId, self::ERROR_STATUS, $message, $data);
	}
	
	/**
	 * @param string $correlationId
	 * @param string $status
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function reply($correlationId, $status, $message, $data = null) {
		return new self($correlationId, $status, $message, $data);
	}
	
	/**
	 * @return string
	 */
	public function getCorrelationId() {
		return $this->correlationId;
	}
	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * @return bool
	 */
	public function isSuccess() {
		return $this->getStatus() === self::SUCCESS_STATUS;
	}
	
	/**
	 * @return bool
	 */
	public function isError() {
		return $this->getStatus() === self::ERROR_STATUS;
	}
	
	/**
	 * @return string
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}
	
	public function jsonSerialize() {
		return [
		  'correlation_id' => $this->getCorrelationId(),
		  'status' => $this->getStatus(),
		  'message' => $this->getMessage(),
		  'data' => $this->getData()
		];
	}
	
	/**
	 * @return string
	 */
	public function toJson() {
		return json_encode($this);
	}
}