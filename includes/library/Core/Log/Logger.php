<?php

declare(strict_types=1);

namespace Core\Log;

class Logger {
	protected array $storages;
	protected int $levels;

	public function __construct(array $storages = [], int $levels = -1) {
		$this->storages = $storages;

		if( $levels <= 0 )
			$this->levels = LEVEL_DEBUG | LEVEL_INFO | LEVEL_WARN | LEVEL_ERROR;
		else
			$this->levels = $levels;
	}

	public function Log(int $level, string $message, array $source, $details = null): void {
		if( ($level & $this->levels) == 0 )
			return;

		foreach($this->storages as $s)
			$s->Log($level, $message, $source, $details);
	}

	private function getSourceInfo(int $depth = 2): array {
		$bt = debug_backtrace(0, $depth);

		return [
			'file' => $bt[$depth-1]['file'],
			'line' => $bt[$depth-1]['line'],
			'class' => $bt[$depth-1]['class'],
			'function' => $bt[$depth-1]['function'],
			'type' => $bt[$depth-1]['type']
		];
	}

	public function Debug(string $message, $details) {
		$source = $this->getSourceInfo();

		$this->Log(\Core\Log\LEVEL_DEBUG, $message, $source, $details);
	}

	public function Info(string $message, $details) {
		$source = $this->getSourceInfo();

		$this->Log(\Core\Log\LEVEL_INFO, $message, $source, $details);
	}

	public function Warn(string $message, $details) {
		$source = $this->getSourceInfo();

		$this->Log(\Core\Log\LEVEL_WARN, $message, $source, $details);
	}

	public function Error(string $message, $details) {
		$source = $this->getSourceInfo();

		$this->Log(\Core\Log\LEVEL_ERROR, $message, $source, $details);
	}
}