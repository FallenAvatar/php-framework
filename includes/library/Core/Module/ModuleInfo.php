<?php declare(strict_types=1);

namespace Core\Module;

class ModuleInfo extends \Core\Obj {
	protected array $config;
	public function _getConfig(): array { return $this->config; }
	
	protected string $dir;
	public function _getDirectory(): string { return $this->dir; }

	protected ?\Core\Module\Base $inst = null;
	public function _getInstance(): ?\Core\Module\Base { return $this->inst; }
	public function _setInstance(\Core\Module\Base $val): void { $this->inst = $val; }

	public function __construct(array $config, string $dir) {
		$this->config = $config;
		$this->dir = $dir;
	}
}