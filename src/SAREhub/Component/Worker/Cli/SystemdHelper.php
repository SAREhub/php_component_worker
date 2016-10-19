<?php

namespace SAREhub\Component\Worker\Cli;

class SystemdHelper {
	
	/**
	 * @param string $unitName
	 */
	public function start($unitName) {
		$this->exec('systemctl start '.$unitName);
	}
	
	/**
	 * @param string $path
	 * @return string
	 */
	public function escape($path) {
		return $this->exec('systemd-escape '.$path);
	}
	
	/**
	 * @param string $command
	 * @return string
	 */
	public function exec($command) {
		return shell_exec($command);
	}
}