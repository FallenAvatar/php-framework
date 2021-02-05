<?php

declare(strict_types=1);

namespace Core\Configuration;

class Config extends \Core\DynObject {
	protected array $files;
	public function __construct(array $config = [], array $files = []) {
		parent::__construct($config, true);

		$this->files = $files;
	}
}