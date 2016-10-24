<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Cli\Cli;
use SAREhub\Component\Worker\Command\CommandService;
use Symfony\Component\Console\Application;

class CliTest extends TestCase {
	
	private $application;
	private $commandService;
	
	/**
	 * @var Cli
	 */
	private $cli;
	
	protected function setUp() {
		parent::setUp();
		
		$this->application = $this->createMock(Application::class);
		$this->commandService = $this->createMock(CommandService::class);
		
		$this->cli = Cli::newInstance()
		  ->withApplication($this->application)
		  ->withCommandService($this->commandService);
	}
	
	public function testRunThenCommandServiceStart() {
		$this->commandService->expects($this->once())->method('start');
		$this->cli->run();
	}
	
	public function testRunThenApplicationRun() {
		$this->application->expects($this->once())->method('run');
		$this->cli->run();
	}
	
	public function testRunThenCommandServiceStop() {
		$this->commandService->expects($this->once())->method('stop');
		$this->cli->run();
	}
	
	public function testGetManagerConfigFilePath() {
		$this->cli = $this->createPartialMock(Cli::class, ['getConfig']);
		$vfsRoot = vfsStream::setup();
		$this->cli->method('getConfig')->willReturn(new Parameters([
		  'manager' => [
			'configRootPath' => $vfsRoot->url()
		  ]
		]));
		$this->assertEquals($vfsRoot->url().'/manager1.php', $this->cli->getManagerConfigFilePath('manager1'));
	}
	
	public function testIsManagerConfigFileExistsWhenNotExistsThenReturnFalse() {
		$this->cli = $this->createPartialMock(Cli::class, ['getConfig']);
		$vfsRoot = vfsStream::setup();
		$this->cli->method('getConfig')->willReturn(new Parameters([
		  'manager' => [
			'configRootPath' => $vfsRoot->url()
		  ]
		]));
		$this->assertFalse($this->cli->isManagerConfigFileExists('manager1'));
	}
	
	public function testIsManagerConfigFileExistsWhenExistsThenReturnTrue() {
		$this->cli = $this->createPartialMock(Cli::class, ['getConfig']);
		$vfsRoot = vfsStream::setup();
		$vfsRoot->addChild(vfsStream::newFile('manager1.php'));
		$this->cli->method('getConfig')->willReturn(new Parameters([
		  'manager' => [
			'configRootPath' => $vfsRoot->url()
		  ]
		]));
		$this->assertTrue($this->cli->isManagerConfigFileExists('manager1'));
	}
}
