<?php

declare(strict_types=1);

namespace Core\Data;

class FuncValue extends SpecialValue {
	public string $TextValue;
	public string $ParamName;
	public $ParamValue;
	
	public function __construct(string $name, string $txt, $val) {
		parent::__construct('func-value');
		$this->TextValue = $txt;
		$this->ParamName = $name;
		$this->ParamValue = $val;
	}
}