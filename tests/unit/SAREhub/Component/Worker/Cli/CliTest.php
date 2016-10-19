<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Cli\Cli;
use SAREhub\Component\Worker\Command\CommandService;
use Symfony\Component\Console\Application;

class CliTest extends TestCase {
	
	private $application;
	private $commandService;
	
	/**
	 * @var Cli
	 */
	private $bootstrap;
	
	protected function setUp() {
		parent::setUp();
		
		$this->application = $this->createMock(Application::class);
		$this->commandService = $this->createMock(CommandService::class);
		
		$this->bootstrap = Cli::newInstance()
		  ->withApplication($this->application)
		  ->withCommandService($this->commandService);
	}
	
	public function testRunThenCommandServiceStart() {
		$this->commandService->expects($this->once())->method('start');
		$this->bootstrap->run();
	}
	
	public function testRunThenApplicationRun() {
		$this->application->expects($this->once())->method('run');
		$this->bootstrap->run();
	}
	
	public function testRunThenCommandServiceStop() {
		$this->commandService->expects($this->once())->method('stop');
		$this->bootstrap->run();
	}
	
	
}
