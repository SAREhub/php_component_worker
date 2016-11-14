<?php

namespace SAREhub\Component\Worker\Command;


interface CommandInputServiceFactory {
	
	public function createCommandInput();
	
	public function createCommandReplyOutput();
}