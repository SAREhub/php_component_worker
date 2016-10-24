<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Cli\Cli;
use SAREhub\Component\Worker\Cli\StartManagerCommand;
use SAREhub\Component\Worker\Cli\SystemdHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StartManagerCommandTest extends TestCase {
	
	private $cli;
	private $systemdHelper;
	
	/**
	 * @var CommandTester
	 */
	private $commandTester;
	
	protected function setUp() {
		parent::setUp();
		
		$this->cli = $this->createMock(Cli::class);
		
		$this->cli->method('getManagerConfigFilePath')->willReturn('path');
		$this->systemdHelper = $this->createMock(SystemdHelper::class);
		$application = new Application();
		$application->add(StartManagerCommand::newInstance()
		  ->withCli($this->cli)
		  ->withSystemdHelper($this->systemdHelper));
		
		$this->commandTester = new CommandTester($application->find('start-manager'));
	}
	
	public function testExecuteWhenConfigFileNotExistsThenPrint() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(false);
		$this->commandTester->execute(['manager' => 'file']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("config file isn't exists", $output);
	}
	
	public function testExecuteWhenNoConfigThenSystemdStartNotCall() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(false);
		$this->systemdHelper->expects($this->never())->method('start');
		$this->commandTester->execute(['manager' => 'file']);
	}
	
	public function testExecuteWhenConfigThenSystemdStart() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->systemdHelper->expects($this->once())->method('start')->with('worker-manager@manager.service');
		$this->commandTester->execute(['manager' => 'manager']);
	}
	
	public function testExecuteWhenConfigThenOutputStarting() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->commandTester->execute(['manager' => 'manager']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("starting manager with config: path", $output);
	}
	
	public function testExecuteWhenConfigThenOutputUnitInstanceName() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->commandTester->execute(['manager' => 'manager']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("manager instance unit name: worker-manager@manager.service", $output);
	}
	
	public function testExecuteWhenConfigThenOutputSystemdStart() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->systemdHelper->method('start')->willReturn('systemd_start');
		$this->commandTester->execute(['manager' => 'manager']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("systemd start output: systemd_start", $output);
	}
	
}
