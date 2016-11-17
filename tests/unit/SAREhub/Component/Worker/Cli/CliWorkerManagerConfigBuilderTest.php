<?php

use Monolog\Handler\SyslogHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use PHPUnit\Framework\TestCase;
use SAREhub\Commons\Misc\Dsn;
use SAREhub\Component\Worker\Cli\CliWorkerManagerConfigBuilder;

class CliWorkerManagerConfigBuilderTest extends TestCase {
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $onStart;
	
	/**
	 * @var PHPUnit_Framework_MockObject_MockObject
	 */
	private $loggingFactory;
	
	/**
	 * @var CliWorkerManagerConfigBuilder
	 */
	private $builder;
	
	protected function setUp() {
		$this->onStart = $this->createPartialMock(stdClass::class, ['__invoke']);
		$this->loggingFactory = $this->createPartialMock(stdClass::class, ['__invoke']);
		$this->builder = CliWorkerManagerConfigBuilder::newInstance()
		  ->withId('managerId')
		  ->withScriptPath('/path/to/script')
		  ->withManagerCommandServiceZmqRootPath('/path/to/zmq')
		  ->withOnStart($this->onStart)
		  ->withLoggingConfigFactory($this->loggingFactory);
	}
	
	public function testCreateThenManagerId() {
		$config = $this->builder->create();
		$this->assertEquals('managerId', $config['id']);
	}
	
	public function testCreateThenOnStart() {
		$config = $this->builder->create();
		$this->assertEquals($this->onStart, $config['onStart']);
	}
	
	public function testCreateThenCallLoggingFactory() {
		$this->loggingFactory->expects($this->once())
		  ->method('__invoke')
		  ->with('managerId');
		$this->builder->create();
	}
	
	public function testCreateThenManagerProcessService() {
		$config = $this->builder->create()['manager']['processService'];
		$expectedConfig = [
		  'runnerScript' => '/path/to/script',
		  'arguments' => [],
		  'workingDirectory' => './'
		];
		
		$this->assertEquals($expectedConfig, $config);
	}
	
	public function testCreateThenManagerCommandService() {
		$config = $this->builder->create()['manager']['commandService'];
		$expectedConfig = [
		  'commandOutput' => [
			'endpoint' => Dsn::ipc()->endpoint('/path/to/zmq/managerId/commandInput')
		  ],
		  'commandReplyInput' => [
			'topic' => 'worker.command.reply',
			'endpoint' => Dsn::ipc()->endpoint('/path/to/zmq/managerId/commandReplyOutput')
		  ]
		];
		
		$this->assertEquals($expectedConfig, $config);
	}
	
	public function testDefaultLoggingFactoryThenSyslogHandler() {
		$factory = CliWorkerManagerConfigBuilder::getDefaultLoggingFactory();
		$loggingConfig = $factory('m');
		$this->assertInstanceOf(SyslogHandler::class, $loggingConfig['handlers'][0]);
	}
	
	public function testDefaultLoggingFactoryThenSyslogHandlerIdent() {
		$factory = CliWorkerManagerConfigBuilder::getDefaultLoggingFactory();
		$loggingConfig = $factory('m');
		/** @var SyslogHandler $syslogHandler */
		$syslogHandler = $loggingConfig['handlers'][0];
		// take ident from handler(no getter, hard game started)
		$ident = ((array)$syslogHandler)["\0*\0ident"];
		$this->assertEquals('m', $ident);
	}
	
	public function testDefaultLoggingFactoryThenProcessor() {
		$factory = CliWorkerManagerConfigBuilder::getDefaultLoggingFactory();
		$loggingConfig = $factory('m');
		$this->assertInstanceOf(PsrLogMessageProcessor::class, $loggingConfig['processors'][0]);
	}
}
