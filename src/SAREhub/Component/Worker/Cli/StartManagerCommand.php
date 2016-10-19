<?php

namespace SAREhub\Component\Worker\Cli;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
		  ->setDefinition(new InputDefinition([
		    new InputOption('manager', 'm', InputOption::VALUE_REQUIRED, 'manager id')
		  ]));
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$managerId = $input->getOption('manager');
		$configPath = $this->getConfigPath($managerId);
		if ($this->isConfigExists($configPath)) {
			$this->getLogger()->info('starting manager with config ', ['config' => $configPath]);
			$output->writeln('starting manager with config: '.$configPath);
			
			$unitName = $this->getManagerUnitInstanceName($managerId);
			$this->getLogger()->info('manager unit instance name: ', ['unit' => $unitName]);
			$output->writeln('manager instance unit name: '.$unitName);
			
			$return = $this->systemdHelper->start($unitName);
			$this->getLogger()->info('systemd start output: '.$return);
			$output->writeln('systemd start output: '.$return);
			
		} else {
			$output->writeln("config file isn't exists");
			$this->getLogger()->warning("config file isn't exists", ['config' => $configPath]);
		}
	}
	
	private function getConfigPath($configFile) {
		$configRootPath = $this->getCliConfig()->getRequiredAsMap('manager')->getRequired('configRootPath');
		return $configRootPath.'/'.$configFile.'.php';
	}
	
	private function isConfigExists($configPath) {
		return file_exists($configPath);
	}
	
	private function getManagerUnitInstanceName($managerId) {
		return 'worker-manager@'.$managerId.'.service';
	}
}