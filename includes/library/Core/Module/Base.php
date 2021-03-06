<?php

declare(strict_types=1);

namespace Core\Module;

class Base extends \Core\Obj {
	protected array $config;
	public function _getConfig(): array { return $this->config; }

	public function __construct(array $config) {
		$this->config = $config;
	}

	public function Init(): void {}
}