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
		$this->getCli()->getCommandService()->process(
		  CommandRequest::newInstance()
			->withTopic($managerId)
			->syncMode()
			->withCommand(WorkerCommands::stop($this->getCli()->getSessionId()))
			->withReplyCallback(function (CommandRequest $request, CommandReply $reply) use ($output) {
				$output->writeln('manager reply: '.$reply->toJson());
			})
		);
	}
}