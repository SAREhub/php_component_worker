<?php
date_default_timezone_set('Europe/Warsaw');

use SAREhub\Commons\Misc\Parameters;
use SAREhub\Component\Worker\Command\Command;
use SAREhub\Component\Worker\Command\CommandReply;
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
	$cliCommandServiceConfig = $cliConfig->getRequiredAsMap('manager')->getRequiredAsMap('forwarders');
	
	$config['runner'] = [
	  'commandInput' => [
		'topic' => '',
		'endpoint' => $cliCommandServiceConfig->getRequiredAsMap('commandOutput')->getRequired('output')
	  ],
	  'commandReplyOutput' => [
		'topic' => 'worker-cli',
		'endpoint' => $cliCommandServiceConfig->getRequiredAsMap('commandReplyInput')->getRequired('input')
	  ]
	];
	
	echo 'listen command on '.$config['runner']['commandInput']['endpoint']
	  .' with topic '.$config['runner']['commandInput']['topic']."\n";
	echo 'sending command reply on '.$config['runner']['commandReplyOutput']['endpoint']
	  .' with topic '.$config['runner']['commandReplyOutput']['topic']."\n";
	
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
			$runner->processCommand($command, function (Command $command, CommandReply $reply) {
				echo $command."\n";
				echo $reply->toJson()."\n";
			});
		}
	}
	while ($runner->isRunning()) {
		$runner->tick();
	}
	$runner->stop();
	
	echo "stopped\n";
}




