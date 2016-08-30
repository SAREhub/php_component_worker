<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Process\Process;

class ProcessStreamCommandOutputTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $processMock;
	private $processInputStream;
	
	/** @var ProcessStreamCommandOutput */
	private $commandOutput;
	
	protected function setUp() {
		$this->processMock = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
		$this->processInputStream = fopen('php://memory', 'w+');
		$this->processMock->method('getInput')->willReturn($this->processInputStream);
		$this->commandOutput = new ProcessStreamCommandOutput($this->processMock);
	}
	
	
	public function testSendCommand() {
		$commandMock = $this->getMockBuilder(WorkerCommand::class)->disableOriginalConstructor()->getMock();
		$commandMock->expects($this->atLeast(2))->method('jsonSerialize')->willReturn([
		  'name' => 'test',
		  'parameters' => []
		]);
		
		$this->commandOutput->sendCommand($commandMock);
		fseek($this->processInputStream, 0);
		$this->assertEquals(json_encode($commandMock), trim(fgets($this->processInputStream)));
		fclose($this->processInputStream);
	}
	
	public function testGetCommandConfirmation() {
		$this->processMock->method('getIncrementalOutput')->willReturn("1\ngfdgdf");
		$this->assertEquals('1', $this->commandOutput->getCommandConfirmation());
		fclose($this->processInputStream);
	}
	
	public function testGetCommandConfirmationForPartialOutputFromProcess() {
		$this->processMock->method('getIncrementalOutput')->willReturnOnConsecutiveCalls("test1", "\nafter", "after");
		$this->assertFalse($this->commandOutput->getCommandConfirmation());
		$this->assertTrue($this->commandOutput->getCommandConfirmation());
		fclose($this->processInputStream);
	}
	
	
}
