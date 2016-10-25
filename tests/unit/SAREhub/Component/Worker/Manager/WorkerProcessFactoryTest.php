<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Manager\WorkerProcessFactory;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

class WorkerProcessFactoryTest extends TestCase {
	
	/**
	 * @expectedException Respect\Validation\Exceptions\ValidationException
	 */
	public function testCreateWhenRunnerScriptPathEmpty() {
		$factory = WorkerProcessFactory::newInstance();
		$factory->create('id');
	}
	
	public function testCreateWhenOnlyRunnerScriptPathSets() {
		$factory = WorkerProcessFactory::newInstance()->withRunnerScriptPath('runner.php');
		$workerProcess = $factory->create('id');
		
		$this->assertSame('id', $workerProcess->getId());
		$process = $workerProcess->getProcess();
		$this->assertInstanceOf(Process::class, $process);
		$expectedCommandLine = ProcessUtils::escapeArgument('php');
		$expectedCommandLine .= ' '.ProcessUtils::escapeArgument('runner.php');
		$expectedCommandLine .= ' '.ProcessUtils::escapeArgument('id');
		
		$this->assertEquals($expectedCommandLine, $process->getCommandLine());
	}
	
	public function testCreateWithCustomWorkingDirectory() {
		$factory = WorkerProcessFactory::newInstance()
		  ->withRunnerScriptPath('runner.php')
		  ->withWorkingDirectory('dir');
		
		$workerProcess = $factory->create('id');
		$process = $workerProcess->getProcess();
		$this->assertEquals('dir', $process->getWorkingDirectory());
	}
	
	public function testCreateWithArguments() {
		$factory = WorkerProcessFactory::newInstance()
		  ->withRunnerScriptPath('runner.php')
		  ->withArguments(['arg1', 'arg2']);
		
		$workerProcess = $factory->create('id');
		$process = $workerProcess->getProcess();
		$this->assertEquals('"php" "runner.php" "id" "arg1" "arg2"', $process->getCommandLine());
	}
}
