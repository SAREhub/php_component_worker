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
		$expectedCommandLine = $this->getCommandLine(['php', 'runner.php', 'id']);
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
		$expectedCommandLine = $this->getCommandLine(['php', 'runner.php', 'id', 'arg1', 'arg2']);
		$this->assertEquals($expectedCommandLine, $process->getCommandLine());
	}
	
	private function getCommandLine(array $arguments) {
		return implode(' ', array_map(array(ProcessUtils::class, 'escapeArgument'), $arguments));
	}
}
