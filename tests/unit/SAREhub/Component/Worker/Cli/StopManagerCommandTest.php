<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Cli\Cli;
use SAREhub\Component\Worker\Cli\StopManagerCommand;
use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandRequest;
use SAREhub\Component\Worker\Command\CommandService;
use SAREhub\Component\Worker\WorkerCommands;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StopManagerCommandTest extends TestCase {
	
	private $cli;
	
	/**
	 * @var CommandTester
	 */
	private $commandTester;
	private $commandService;
	
	protected function setUp() {
		parent::setUp();
		
		$this->cli = $this->createMock(Cli::class);
		$this->commandService = $this->createMock(CommandService::class);
		
		$this->cli->method('getCommandService')->willReturn($this->commandService);
		$application = new Application();
		$application->add(StopManagerCommand::newInstance()
		  ->withCli($this->cli));
		
		$this->commandTester = new CommandTester($application->find('stop-manager'));
	}
	
	public function testExecuteWhenConfigNotExistsThenNotCommandServiceProcess() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(false);
		$this->commandService->expects($this->never())->method('process');
		$this->commandTester->execute(['manager' => 'm']);
	}
	
	public function testExecuteWhenConfigNotExistsThenOutputInfo() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(false);
		$this->commandTester->execute(['manager' => 'm']);
		$this->assertContains("manager isn't exists", $this->commandTester->getDisplay());
	}
	
	public function testExecuteWhenConfigThenCommandServiceProcess() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->commandService->expects($this->once())->method('process');
		$this->commandTester->execute(['manager' => 'm']);
	}
	
	public function testExecuteThenCommandRequestTopicIsManagerId() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->assertCommandRequest(function (CommandRequest $request) {
			return $request->getTopic() === 'm';
		});
		$this->commandTester->execute(['manager' => 'm']);
	}
	
	public function testExecuteThenCommandRequestNotAsync() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->assertCommandRequest(function (CommandRequest $request) {
			return !$request->isAsync();
		});
		$this->commandTester->execute(['manager' => 'm']);
	}
	
	public function testExecuteThenCommandRequestCommandWorkerStop() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$this->assertCommandRequest(function (CommandRequest $request) {
			return $request->getCommand()->getName() === WorkerCommands::STOP;
		});
		$this->commandTester->execute(['manager' => 'm']);
	}
	
	public function testExecuteWhenReplyThenCommandRequestReplyCallbackOutput() {
		$this->cli->method('isManagerConfigFileExists')->willReturn(true);
		$reply = CommandReply::success('1', 'm');
		$this->assertCommandRequest(function (CommandRequest $request) use ($reply) {
			($request->getReplyCallback())($request, $reply);
			return true;
		});
		
		$this->commandTester->execute(['manager' => 'm']);
		$this->assertContains('manager reply: '.$reply->toJson(), $this->commandTester->getDisplay());
		
	}
	
	private function assertCommandRequest(callable $callback) {
		$this->commandService->expects($this->once())->method('process')
		  ->with($this->callback($callback));
	}
}
