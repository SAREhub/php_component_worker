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
		  ->setDescription('Starts new worker manager with selected config')
		  ->setHelp("Starts worker manager with config from file. Check example config: cli/workerManagerConfigExample.php ")
		  ->setDefinition(new InputDefinition([
			new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to manager config file')
		  ]));
	}
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$configFile = $input->getOption('config');
		$configPath = $this->getConfigPath($configFile);
		if ($this->isConfigExists($configPath)) {
			$this->getLogger()->info('starting manager with config ', ['config' => $configPath]);
			$output->writeln('starting manager with config: '.$configPath);
			
			$unitName = $this->getManagerUnitInstanceName($configFile);
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
		return $configRootPath.'/'.$configFile;
	}
	
	private function isConfigExists($configPath) {
		return file_exists($configPath);
	}
	
	private function getManagerUnitInstanceName($configFile) {
		$escappedFile = $this->systemdHelper->escape($configFile);
		return 'worker-manager@'.$escappedFile;
	}
}