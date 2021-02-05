<?php

declare(strict_types=1);

namespace Core\Autoload;

class Exception extends \Core\Exception {
	public function __construct(string $msg = 'An error has occured in the autoloader.') {
		parent::__construct($msg);
	}
}