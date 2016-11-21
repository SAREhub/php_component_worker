<?php
date_default_timezone_set('Europe/Warsaw');

use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Cli\WorkerManagerBootstrap;
use SAREhub\Component\Worker\WorkerContext;

require dirname(__DIR__).'/vendor/autoload.php';

$cliConfig = new Parameters(include(__DIR__.'/config.php'));
$cliManagerConfig = $cliConfig->getRequiredAsMap('manager');

$managerId = $argv[1];
echo "starting manager with id: ".$managerId."\n";
$configFile = $managerId.'.php';

$configPath = $cliManagerConfig->getRequired('configRootPath').'/'.$configFile;
echo "Config file path: ".$configPath."\n";

if (file_exists($configPath)) {
	$config = include($configPath);
	echo "config loaded\n";
	
	$config['runner'] = [
	  'commandInput' => [
	    'endpoint' => $cliManagerConfig->getRequired('commandInputEndpoint')
	  ],
	  'commandReplyOutput' => [
		'topic' => 'worker-cli',
	    'endpoint' => $cliManagerConfig->getRequired('commandReplyOutputEndpoint')
	  ]
	];
	
	echo 'listen command on '.$config['runner']['commandInput']['endpoint']."\n";
	echo 'sending command reply on '.$config['runner']['commandReplyOutput']['endpoint']
	  .' with topic '.$config['runner']['commandReplyOutput']['topic']."\n";
	
	$config = new Parameters($config);
	
	$workerContext = WorkerContext::newInstance()
	  ->withId($config->getRequired('id'))
	  ->withRootPath(getcwd());
	
	$runner = WorkerManagerBootstrap::newInstance()
	  ->withWorkerContext($workerContext)
	  ->withConfig($config)
	  ->build();
	
	$runner->start();
	if ($runner->isRunning()) {
		$onStart = $config->getRequired('onStart');
		$onStart($runner);
	}
	
	while ($runner->isRunning()) {
		$runner->tick();
		usleep(100);
	}
	
	echo "stopped\n";
}




