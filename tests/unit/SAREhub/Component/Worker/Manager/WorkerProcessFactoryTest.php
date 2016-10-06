<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Manager\WorkerProcessFactory;
use Symfony\Component\Process\Process;

class WorkerProcessFactoryTest extends TestCase {
	
	/**
	 * @expectedException Respect\Validation\Exceptions\ValidationException
	 */
	public function testCreateWhenRunnerScriptPathEmpty() {
		$factory = WorkerProcessFactory::newInstance();
		$factory->create('uuid');
	}
	
	public function testCreateWhenOnlyRunnerScriptPathSets() {
		$factory = WorkerProcessFactory::newInstance()->withRunnerScriptPath('runner.php');
		$workerProcess = $factory->create('uuid');
		
		$this->assertSame('uuid', $workerProcess->getUuid());
		$process = $workerProcess->getProcess();
		$this->assertInstanceOf(Process::class, $process);
		$this->assertEquals('"php" "runner.php" "uuid"', $process->getCommandLine());
	}
	
	public function testCreateWithCustomWorkingDirectory() {
		$factory = WorkerProcessFactory::newInstance()
		  ->withRunnerScriptPath('runner.php')
		  ->withWorkingDirectory('dir');
		
		$workerProcess = $factory->create('uuid');
		$process = $workerProcess->getProcess();
		$this->assertEquals('dir', $process->getWorkingDirectory());
	}
	
	public function testCreateWithArguments() {
		$factory = WorkerProcessFactory::newInstance()
		  ->withRunnerScriptPath('runner.php')
		  ->withArguments(['arg1', 'arg2']);
		
		$workerProcess = $factory->create('uuid');
		$process = $workerProcess->getProcess();
		$this->assertEquals('"php" "runner.php" "uuid" "arg1" "arg2"', $process->getCommandLine());
	}
}
