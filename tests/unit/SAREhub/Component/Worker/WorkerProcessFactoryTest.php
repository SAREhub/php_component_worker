<?php

namespace SAREhub\Component\Worker;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;
use SAREhub\Component\Worker\Command\CommandOutput;
use Symfony\Component\Process\Process;

class WorkerProcessFactoryTest extends TestCase {
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandOutputFactoryMock;
	
	/** @var PHPUnit_Framework_MockObject_MockObject */
	private $commandOutputMock;
	
	/** @var WorkerProcessFactory */
	private $factory;
	
	/** @var WorkerInfo */
	private $workerInfo;
	
	protected function setUp() {
		$this->commandOutputFactoryMock = $this->getMockBuilder(\stdClass::class)
		  ->setMethods(['__invoke'])
		  ->getMock();
		
		$this->commandOutputMock = $this->createMock(CommandOutput::class);
		
		$this->commandOutputFactoryMock->expects($this->once())
		  ->method('__invoke')
		  ->with($this->isInstanceOf(Process::class))
		  ->willReturn($this->commandOutputMock);
		
		
		$this->factory = WorkerProcessFactory::getForRunnerScript('runner.php')
		  ->commandOutputFactory($this->commandOutputFactoryMock);
		
		$this->workerInfo = new WorkerInfo();
	}
	
	public function testCreate() {
		$workerProcess = $this->factory->create($this->workerInfo);
		$this->assertSame($this->workerInfo, $workerProcess->getWorkerInfo());
		$this->assertSame($this->commandOutputMock, $workerProcess->getCommandOutput());
		$process = $workerProcess->getProcess();
		$this->assertInstanceOf(Process::class, $process);
		$this->assertEquals('"php" "runner.php" ', $process->getCommandLine());
	}
	
	public function testCreateWithCustomWorkingDirectory() {
		$workerProcess = $this->factory->workingDirectory('testdir')->create($this->workerInfo);
		$process = $workerProcess->getProcess();
		$this->assertEquals('testdir', $process->getWorkingDirectory());
	}
	
	public function testCreateWithArguments() {
		$workerProcess = $this->factory->arguments(['arg1', 'arg2'])->create($this->workerInfo);
		$process = $workerProcess->getProcess();
		$this->assertEquals('"php" "runner.php" "arg1" "arg2" ', $process->getCommandLine());
	}
}
