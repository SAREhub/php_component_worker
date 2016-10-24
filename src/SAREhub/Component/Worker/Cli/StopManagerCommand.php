<?php

namespace SAREhub\Component\Worker\Cli;


use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandRequest;
use SAREhub\Component\Worker\WorkerCommands;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopManagerCommand extends CliCommand {
	
	public static function newInstance() {
		return new self();
	}
	
	protected function configure() {
		$this
		  ->setName('stop-manager')
		  ->setDescription('stops selected worker manager')
		  ->addArgument('manager', InputArgument::REQUIRED, 'manager id');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$managerId = $input->getArgument('manager');
		if ($this->getCli()->isManagerConfigFileExists($managerId)) {
			$request = $this->createStopCommandRequest($managerId, $output);
			$this->getCli()->getCommandService()->process($request);
		} else {
			$output->writeln("manager doesn't exists");
		}
	}
	
	private function createStopCommandRequest($managerId, $output) {
		return CommandRequest::newInstance()
		  ->withTopic($managerId)
		  ->syncMode()
		  ->withCommand(WorkerCommands::stop($this->getCli()->getSessionId()))
		  ->withReplyCallback($this->createReplyCallback($output));
	}
	
	private function createReplyCallback($output) {
		return function (CommandRequest $request, CommandReply $reply) use ($output) {
			$output->writeln('manager reply: '.$reply->toJson());
		};
	}
}