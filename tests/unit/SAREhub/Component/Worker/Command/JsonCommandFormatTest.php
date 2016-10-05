<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;

class JsonCommandFormatTest extends TestCase {
	
	public function testMarshal() {
		$command = new BasicCommand('name1', ['param1' => 'value']);
		$expectedJson = json_encode(
		  [
			'name' => 'name1',
			'parameters' => [
			  'param1' => 'value'
			]
		  ]);
		
		$this->assertEquals($expectedJson, JsonCommandFormat::newInstance()->marshal($command));
	}
	
	public function testUnmarshal() {
		$command = JsonCommandFormat::newInstance()->unmarshal(json_encode(
		  [
			'name' => 'name1',
			'parameters' => [
			  'param1' => 1
			]
		  ]
		));
		
		$this->assertEquals('name1', $command->getName());
		$this->assertEquals(['param1' => 1], $command->getParameters());
	}
}
