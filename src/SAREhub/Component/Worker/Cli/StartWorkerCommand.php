<?php

namespace SAREhub\Component\Worker\Cli;

use SAREhub\Component\Worker\Command\CommandReply;
use SAREhub\Component\Worker\Command\CommandRequest;
use SAREhub\Component\Worker\Manager\ManagerCommands;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartWorkerCommand extends CliCommand {
	
	public static function newInstance() {
		return new self();
	}
	
	protected function configure() {
		$this
		  ->setName('start-worker')
		  ->setDescription('starts worker in selected manager')
		  ->addArgument('manager', InputArgument::REQUIRED, 'manager id')
		  ->addArgument('worker', InputArgument::REQUIRED, 'worker id');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$managerId = $input->getArgument('manager');
		$workerId = $input->getArgument('worker');
		
		if ($this->getCli()->isManagerConfigFileExists($managerId)) {
			$this->getCli()->getCommandService()->process(
			  CommandRequest::newInstance()
				->withTopic($managerId)
				->syncMode()
				->withCommand(ManagerCommands::start($this->getCli()->getSessionId(), $workerId))
				->withReplyCallback(function (CommandRequest $request, CommandReply $reply) use ($output) {
					$output->writeln('<info>manager reply: </info>'.$reply->toJson());
				})
			);
		} else {
			$output->writeln("<error>manager isn't exists</error>");
		}
	}
	
}