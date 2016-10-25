<?php

namespace SAREhub\Component\Worker\Cli;


use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandRequest;
use SAREhub\Component\Worker\Manager\ManagerCommands;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StopWorkerCommand extends CliCommand {
	
	public static function newInstance() {
		return new self();
	}
	
	protected function configure() {
		$this
		  ->setName('stop-worker')
		  ->setDescription('stops worker in manager')
		  ->addArgument('manager', InputArgument::REQUIRED, 'manager id')
		  ->addArgument('worker', InputArgument::REQUIRED, 'manager id');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$managerId = $input->getArgument('manager');
		$workerId = $input->getArgument('worker');
		
		if ($this->getCli()->isManagerConfigFileExists($managerId)) {
			$this->getCli()->getCommandService()->process(
			  CommandRequest::newInstance()
				->withTopic($managerId)
				->syncMode()
				->withCommand(ManagerCommands::stop($this->getCli()->getSessionId(), $workerId))
				->withReplyCallback(function (CommandRequest $request, CommandReply $reply) use ($output) {
					$output->writeln('manager reply: '.$reply->toJson());
				})
			);
		} else {
			$output->writeln("<error>manager isn't exists</error>");
		}
	}
}