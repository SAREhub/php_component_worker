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
}
