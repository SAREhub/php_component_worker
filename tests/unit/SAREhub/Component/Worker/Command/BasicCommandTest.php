<?php


use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Command\BasicCommand;

class BasicCommandTest extends TestCase {
	
	public function testCreateWithoutParameters() {
		$command = new BasicCommand('1', 'name');
		$this->assertEquals('1', $command->getCorrelationId());
		$this->assertEquals('name', $command->getName());
		$this->assertEquals([], $command->getParameters());
	}
	
	public function testCreateWithParameters() {
		$parameters = ['param1' => 1];
		$command = new BasicCommand('1', 'name', $parameters);
		$this->assertEquals('name', $command->getName());
		$this->assertEquals($parameters, $command->getParameters());
	}
	
	public function testToString() {
		$command = new BasicCommand('1', 'name1', ['param1' => 1]);
		
		$expectedJson = json_encode([
		  'correlation_id' => '1',
		  'name' => 'name1',
		  'parameters' => [
			'param1' => 1
		  ]
		]);
		$this->assertEquals('COMMAND:'.$expectedJson, (string)$command);
	}
}
