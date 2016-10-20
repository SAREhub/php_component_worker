<?php
date_default_timezone_set('Europe/Warsaw');

use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Manager\WorkerManagerBootstrap;
use SAREhub\Component\Worker\WorkerContext;

require dirname(__DIR__).'/vendor/autoload.php';

$cliConfig = new Parameters(include(__DIR__.'/config.php'));
$cliManagerConfig = $cliConfig->getRequiredAsMap('manager');
$managerId = $argv[1];
echo "starting manager with id: ".$managerId;

$configFile = $managerId.'.php';
echo "Config file: ".$configFile."\n";
$configPath = $cliManagerConfig->getRequired('configRootPath').'/'.$configFile;
echo "Config file path: ".$configPath."\n";

if (file_exists($configPath)) {
	echo "loading config\n";
	$config = include($configPath);
	echo "config loaded\n";
	$runner = WorkerManagerBootstrap::newInstance()
	  ->withWorkerContext(WorkerContext::newInstance()
		->withId($config['id'])
		->withRootPath(getcwd())
	  )->withConfig($config)->build();
	
	$runner->start();
	if ($runner->isRunning()) {
		$config = new Parameters($config);
		$startCommands = $config->getRequiredAsMap('manager')->getRequired('startCommands');
		foreach ($startCommands as $command) {
			$runner->processCommand($command, function ($command, $reply) {
				var_dump($command);
				var_dump($reply);
			});
		}
	}
	while ($runner->isRunning()) {
		$runner->tick();
	}
	$runner->stop();
	
	echo "stopped\n";
}




