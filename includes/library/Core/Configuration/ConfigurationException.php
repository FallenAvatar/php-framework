<?php

declare(strict_types=1);

namespace Core\Configuration;

class ConfigurationException extends \Core\Exception {
	public function __construct(string $msg = 'A configuration error has been detected.') {
		parent::__construct($msg);
	}
}