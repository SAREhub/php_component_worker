<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;

class BasicCommandTest extends TestCase {
	
	
	public function testCreateWithoutParameters() {
		$command = new BasicCommand('name');
		$this->assertEquals('name', $command->getName());
		$this->assertEquals([], $command->getParameters());
	}
	
	public function testCreateWithParameters() {
		$parameters = ['param1' => 1];
		$command = new BasicCommand('name', $parameters);
		$this->assertEquals('name', $command->getName());
		$this->assertEquals($parameters, $command->getParameters());
	}
	
	public function testToString() {
		$command = new BasicCommand('name1', ['param1' => 1]);
		
		$expectedJson = json_encode([
		  'name' => 'name1',
		  'parameters' => [
			'param1' => 1
		  ]
		]);
		$this->assertEquals(BasicCommand::class.':'.$expectedJson, (string)$command);
	}
}
