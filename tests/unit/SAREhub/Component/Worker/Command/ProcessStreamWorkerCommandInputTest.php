<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;

class ProcessStreamWorkerCommandInputTest extends TestCase {
	
	private $inStream;
	private $outStream;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandDeserializerMock;
	
	/** @var ProcessStreamCommandInput */
	private $commandInput;
	
	protected function setUp() {
		$this->commandMock = $this->getMockBuilder(WorkerCommand::class)->getMock();
		$this->commandDeserializerMock = $this->getMockBuilder(\stdClass::class)->setMethods(['__invoke'])->getMock();
		$this->commandDeserializerMock->method('__invoke')->willReturn($this->commandMock);
		
		$this->inStream = fopen("php://memory", 'w+');
		$this->outStream = fopen("php://memory", 'w+');
		$this->commandInput = new ProcessStreamCommandInput($this->inStream, $this->outStream, $this->commandDeserializerMock);
		
	}
	
	public function testGetNextCommandWhenNoCommandSent() {
		$this->assertNull($this->commandInput->getNextCommand());
	}
	
	public function testGetNextCommand() {
		$commandJson = 'testJson';
		$this->commandDeserializerMock->expects($this->once())
		  ->method('__invoke')
		  ->with($commandJson)
		  ->willReturn($this->commandMock);
		
		fwrite($this->inStream, $commandJson."\n");
		fseek($this->inStream, 0);
		$this->assertSame($this->commandMock, $this->commandInput->getNextCommand());
	}
}
