<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;

class ProcessStreamWorkerCommandInputTest extends TestCase {
	
	private $inStream;
	private $outStream;
	
	/** @var ProcessStreamWorkerCommandInput */
	private $commandInput;
	
	protected function setUp() {
		$this->inStream = fopen("php://memory", 'w+');
		$this->outStream = fopen("php://memory", 'w+');
		$this->commandInput = new ProcessStreamWorkerCommandInput($this->inStream, $this->outStream);
	}
	
	public function testGetNextCommandWhenNoCommandSent() {
		$this->assertNull($this->commandInput->getNextCommand());
	}
	
	public function testGetNextCommand() {
		$commandParameters = ['testParam' => 1];
		fwrite($this->inStream, json_encode([
			'name' => 'test',
			'parameters' => $commandParameters
		  ])."\n");
		
		fseek($this->inStream, 0);
		$command = $this->commandInput->getNextCommand();
		$this->assertInstanceOf(WorkerCommand::class, $command);
		$this->assertEquals('test', $command->getName());
		$this->assertEquals($commandParameters, $command->getParameters());
	}
}
