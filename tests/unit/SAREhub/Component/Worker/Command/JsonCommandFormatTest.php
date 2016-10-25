<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\BasicCommand;
use SAREhub\Component\Worker\Command\JsonCommandFormat;

class JsonCommandFormatTest extends TestCase {
	
	private $commandJson;
	
	/**
	 * @var JsonCommandFormat
	 */
	private $format;
	
	protected function setUp() {
		parent::setUp();
		$this->commandJson = json_encode([
		  JsonCommandFormat::CORRELATION_ID_INDEX => '1',
		  JsonCommandFormat::NAME_INDEX => 'name1',
		  JsonCommandFormat::PARAMETERS_INDEX => [
			'param1' => 'value'
		  ]
		]);
		
		$this->format = JsonCommandFormat::newInstance();
	}
	
	public function testMarshal() {
		$command = new BasicCommand('1', 'name1', ['param1' => 'value']);
		$this->assertEquals($this->commandJson, $this->format->marshal($command));
	}
	
	public function testUnmarshal() {
		$command = $this->format->unmarshal($this->commandJson);
		$this->assertEquals('1', $command->getCorrelationId());
		$this->assertEquals('name1', $command->getName());
		$this->assertEquals(['param1' => 'value'], $command->getParameters());
	}
}
