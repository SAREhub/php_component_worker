<?php

namespace SAREhub\Component\Worker\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Component\Process\Process;

class ProcessStreamCommandOutputTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $processMock;
	
	/** @var \resource */
	private $processInputStream;
	
	/** @var ProcessStreamCommandOutput */
	private $commandOutput;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandMock;
	
	protected function setUp() {
		$this->processMock = $this->getMockBuilder(Process::class)->disableOriginalConstructor()->getMock();
		$this->processInputStream = fopen('php://memory', 'w+');
		$this->processMock->method('getInput')->willReturn($this->processInputStream);
		
		$this->commandOutput = new ProcessStreamCommandOutput($this->processMock, function (Command $command) {
			return json_encode([
			  'name' => $command->getName()
			]);
			
		});
		
		$this->commandMock = $this->getMockBuilder(WorkerCommand::class)->getMock();
		$this->commandMock->method('getName')->willReturn('command');
	}
	
	
	public function testSendCommand() {
		$this->commandOutput->sendCommand($this->commandMock);
		fseek($this->processInputStream, 0);
		$this->assertEquals(json_encode(['name' => 'command']), trim(fgets($this->processInputStream)));
		fclose($this->processInputStream);
	}
	
	public function testGetCommandReply() {
		$this->processMock->method('getIncrementalOutput')->willReturn("###1###");
		$this->assertEquals('1', $this->commandOutput->getCommandReply());
		fclose($this->processInputStream);
	}
	
	public function testGetCommandReplyForPartialOutputFromProcess() {
		$this->processMock->method('getIncrementalOutput')->willReturnOnConsecutiveCalls("test###1", "###\nafter", "after");
		$this->assertNull($this->commandOutput->getCommandReply());
		$this->assertEquals('1', $this->commandOutput->getCommandReply());
		$this->assertNull($this->commandOutput->getCommandReply());
		fclose($this->processInputStream);
	}
	
	
}
