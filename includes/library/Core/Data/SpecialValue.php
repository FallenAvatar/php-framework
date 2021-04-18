<?php declare(strict_types=1);

namespace Core\Data;

class SpecialValue extends \Core\Obj {
	public static \Core\Data\SpecialValue $isNull;
	public static \Core\Data\SpecialValue $isNotNull;
	
	protected string $name;
	public function _getName(): string { return $this->name; }
	public function __construct(string $name) {
		$this->name = $name;
	}
}

\Core\Data\SpecialValue::$isNull = new SpecialValue('is-null');
\Core\Data\SpecialValue::$isNotNull = new SpecialValue('is-not-null');