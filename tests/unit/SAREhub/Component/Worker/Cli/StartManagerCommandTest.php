<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Cli\Cli;
use SAREhub\Component\Worker\Cli\StartManagerCommand;
use SAREhub\Component\Worker\Cli\SystemdHelper;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StartManagerCommandTest extends TestCase {
	
	private $vfsRoot;
	
	private $cli;
	private $systemdHelper;
	
	/**
	 * @var CommandTester
	 */
	private $commandTester;
	
	protected function setUp() {
		parent::setUp();
		
		$this->vfsRoot = vfsStream::setup('root');
		$this->cli = $this->createMock(Cli::class);
		$this->cli->method('getConfig')->willReturn(new Parameters([
		  'manager' => [
			'configRootPath' => $this->vfsRoot->url()
		  ]
		]));
		$this->systemdHelper = $this->createMock(SystemdHelper::class);
		$application = new Application();
		$application->add(StartManagerCommand::newInstance()
		  ->withCli($this->cli)
		  ->withSystemdHelper($this->systemdHelper));
		
		$this->commandTester = new CommandTester($application->find('start-manager'));
	}
	
	public function testExecuteWhenConfigFileNotExistsThenPrint() {
		$this->commandTester->execute(['-c' => 'file']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("config file isn't exists", $output);
	}
	
	public function testExecuteWhenNoConfigThenSystemdStartNotCall() {
		$this->systemdHelper->expects($this->never())->method('start');
		$this->commandTester->execute(['-c' => 'file']);
	}
	
	public function testExecuteWhenConfigThenSystemdStart() {
		$this->vfsRoot->addChild(vfsStream::newFile('config.php'));
		$this->systemdHelper->method('escape')->willReturn('escapped');
		$this->systemdHelper->expects($this->once())->method('start')->with('worker-manager@escapped');
		$this->commandTester->execute(['-c' => 'config.php']);
	}
	
	public function testExecuteWhenConfigThenCallSystemdEscape() {
		$this->vfsRoot->addChild(vfsStream::newFile('config.php'));
		$this->systemdHelper->expects($this->once())->method('escape')
		  ->with('config.php');
		$this->commandTester->execute(['-c' => 'config.php']);
	}
	
	public function testExecuteWhenConfigThenOutputStarting() {
		$this->vfsRoot->addChild(vfsStream::newFile('config.php'));
		$this->commandTester->execute(['-c' => 'config.php']);
		
		$configPath = $this->vfsRoot->url().'/config.php';
		$output = $this->commandTester->getDisplay();
		$this->assertContains("starting manager with config: ".$configPath, $output);
	}
	
	public function testExecuteWhenConfigThenOutputUnitInstanceName() {
		$this->vfsRoot->addChild(vfsStream::newFile('config.php'));
		$this->systemdHelper->method('escape')->willReturn('escapped');
		$this->commandTester->execute(['-c' => 'config.php']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("manager instance unit name: worker-manager@escapped", $output);
	}
	
	public function testExecuteWhenConfigThenOutputSystemdStart() {
		$this->vfsRoot->addChild(vfsStream::newFile('config.php'));
		$this->systemdHelper->method('start')->willReturn('systemd_start');
		$this->commandTester->execute(['-c' => 'config.php']);
		$output = $this->commandTester->getDisplay();
		$this->assertContains("systemd start output: systemd_start", $output);
	}
}
