<?php

declare(strict_types=1);

namespace Core\Log;

class Wrapper extends \Core\Log\Handler\BaseHandler implements \Core\Log\Handler\IHandler {
	private array $loggers;
	public function __construct(array $loggers = []) {
		$this->loggers = $loggers;
	}

	public function AddLogger(\Core\Log\Logger $logger) {
		$this->loggers[] = $logger;
	}

	public function Log(int $level, string $message, string $source, $details = null) {
		foreach($this->loggers as $logger) {
			$logger->Log($level, $message, $source, $details);
		}
	}
}