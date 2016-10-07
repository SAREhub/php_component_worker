<?php

namespace SAREhub\Component\Worker\Command;

class CommandReply implements \JsonSerializable {
	
	const SUCCESS_STATUS = 'success';
	const ERROR_STATUS = 'error';
	
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
	
	protected function __construct($status, $message, $data) {
		$this->status = $status;
		$this->message = $message;
		$this->data = $data;
	}
	
	/**
	 * @param array $reply
	 * @return CommandReply
	 */
	public static function createFromArray(array $reply) {
		return new self($reply['status'], $reply['message'], $reply['data']);
	}
	
	/**
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function success($message, $data = null) {
		return self::reply(self::SUCCESS_STATUS, $message, $data);
	}
	
	/**
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function error($message, $data = null) {
		return self::reply(self::ERROR_STATUS, $message, $data);
	}
	
	/**
	 * @param string $status
	 * @param string $message
	 * @param mixed $data
	 * @return CommandReply
	 */
	public static function reply($status, $message, $data = null) {
		return new self($status, $message, $data);
	}
	
	/**
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
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
		  'status' => $this->getStatus(),
		  'message' => $this->getMessage(),
		  'data' => $this->getData()
		];
	}
}