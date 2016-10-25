<?php

use PHPUnit\Framework\TestCase;
use SAREhub\Component\Worker\Cli\SystemdHelper;

class SystemdHelperTest extends TestCase {
	
	/**
	 * @var SystemdHelper
	 */
	private $helper;
	
	protected function setUp() {
		parent::setUp();
		$this->helper = $this->createPartialMock(SystemdHelper::class, ['exec']);
	}
	
	public function testStart() {
		$this->helper->expects($this->once())->method('exec')->with('systemctl start unit');
		$this->helper->start('unit');
	}
	
	public function testEscape() {
		$this->helper->expects($this->once())->method('exec')
		  ->with('systemd-escape path')->willReturn('escaped');
		$this->assertEquals('escaped', $this->helper->escape('path'));
	}
}
