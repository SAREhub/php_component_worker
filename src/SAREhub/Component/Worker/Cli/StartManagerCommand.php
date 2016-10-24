<?php

namespace SAREhub\Component\Worker\Cli;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StartManagerCommand extends CliCommand {
	
	/**
	 * @var SystemdHelper
	 */
	private $systemdHelper;
	
	
	/**
	 * @return StartManagerCommand
	 */
	public static function newInstance() {
		return new self();
	}
	
	/**
	 * @param SystemdHelper $helper
	 * @return $this
	 */
	public function withSystemdHelper(SystemdHelper $helper) {
		$this->systemdHelper = $helper;
		return $this;
	}
	
	protected function configure() {
		$this->setName('start-manager')
		  ->setDescription('Starts selected worker manager')
		  ->setHelp("Starts worker manager with config from file. Check example config: cli/workerManagerConfigExample.php ")
		  ->addArgument('manager', InputArgument::REQUIRED, 'manager id to start');
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$managerId = $input->getArgument('manager');
		if ($this->getCli()->isManagerConfigFileExists($managerId)) {
			$configPath = $this->getCli()->getManagerConfigFilePath($managerId);
			$this->getLogger()->info('starting manager with config ', ['config' => $configPath]);
			$output->writeln('starting manager with config: '.$configPath);
			
			$unitName = $this->getManagerUnitInstanceName($managerId);
			$this->getLogger()->info('manager unit instance name: ', ['unit' => $unitName]);
			$output->write('manager instance unit name: '.$unitName.' ');
			
			$return = $this->systemdHelper->start($unitName);
			if (!empty($return)) {
				$this->getLogger()->info('systemd start output: '.$return);
				$output->writeln('systemd start output: '.$return);
			} else {
				$output->writeln('started');
			}
		} else {
			$configPath = $this->getCli()->getManagerConfigFilePath($managerId);
			$output->writeln("config file isn't exists");
			$this->getLogger()->warning("config file isn't exists", ['config' => $configPath]);
		}
	}
	
	private function getManagerUnitInstanceName($managerId) {
		return 'worker-manager@'.$managerId.'.service';
	}
}