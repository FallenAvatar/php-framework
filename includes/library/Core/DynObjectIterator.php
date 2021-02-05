<?php

declare(strict_types=1);

namespace Core;

class DynObjectIterator implements \Iterator {
	private array $aValues;
	private array $aKeys;
	private int $iCurrent;
	private int $iLen;

	public function __construct(array $arr) {
		$this->iCurrent = 0;
		$this->iLen = count($arr);
		$this->aKeys = [];
		$this->aValues = [];

		foreach($arr as $k => $v) {
			$this->aKeys[] = $k;
			$this->aValues[] = $v;
		}
	}

	public function current() {
		$v = $this->aValues[$this->iCurrent];

		if( is_array($v) && ArrayHelper::IsAssoc($v) )
			$v = new DynObject($v, true, false);

		return $v;
	}

	public function key() {
		return $this->aKeys[$this->iCurrent];
	}

	public function next(): void {
		$this->iCurrent++;
	}

	public function rewind(): void {
		$this->iCurrent = 0;
	}

	public function valid(): bool {
		return ($this->iCurrent < $this->iLen);
	}
}