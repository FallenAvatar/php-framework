<?php declare(strict_types=1);

namespace Core\Data;

class LikeValue extends SpecialValue {
	public $Value;
	
	public function __construct($val) {
		parent::__construct('like');
		$this->Value = $val;
	}
}