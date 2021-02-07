<?php

declare(strict_types=1);

namespace Core\Data;

class LitervalValue extends SpecialValue {
	public $TextValue;
	
	public function __construct(string $name, string $val) {
		parent::__construct($name);
		$this->TextValue = $val;
	}
}