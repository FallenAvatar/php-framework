<?php

namespace Core\Data {
	class FuncValue extends SpecialValue {
		public $TextValue;
		public $ParamName;
		public $ParamValue;
		
		public function __construct($name, $txt, $val) {
			parent::__construct('func-value');
			$this->TextValue = $txt;
			$this->ParamName = $name;
			$this->ParamValue = $val;
		}
	}
}