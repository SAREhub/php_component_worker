<?php
use SAREhub\Commons\Misc\Parameters;
use SAREhub\Commons\Process\PcntlSignals;
use SAREhub\Commons\Zmq\PublishSubscribe\Publisher;
use SAREhub\Commons\Zmq\PublishSubscribe\Subscriber;
use SAREhub\Commons\Zmq\PublishSubscribe\ZmqForwarderDevice;

date_default_timezone_set('Europe/Warsaw');
require dirname(__DIR__).'/vendor/autoload.php';

$type = $argv[1];

$cliConfig = new Parameters(include(__DIR__.'/config.php'));
$cliManagerConfig = $cliConfig->getRequiredAsMap('manager');

$zmqContext = new ZMQContext();
$forwarderConfig = $cliManagerConfig->getRequiredAsMap('forwarders')->getRequiredAsMap($type);

$device = ZmqForwarderDevice::getBuilder()
  ->frontend(Subscriber::inContext($zmqContext)
	->bind($forwarderConfig->getRequired('input'))
  )->backend(Publisher::inContext($zmqContext)
	->bind($forwarderConfig->getRequired('output'))
  )->build();

$canRun = true;
$onStop = function () use (&$canRun) {
	$canRun = false;
};

PcntlSignals::getGlobal()
  ->handle(PcntlSignals::SIGINT, $onStop)
  ->handle(PcntlSignals::SIGTERM, $onStop)
  ->install();
$device->setTimerCallback(function () use (&$canRun) {
	PcntlSignals::getGlobal()->checkPendingSignals();
	return $canRun;
});

$device->run();

