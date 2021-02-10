<?php

declare(strict_types=1);

namespace Core;

class DynObject extends Obj implements \IteratorAggregate {
	protected array $internalData;
	protected bool $isReadOnly;
	protected bool $allowNewProps;

	public function IsReadOnly() { return $this->isReadOnly; }

	public function __construct(array $arr_props = [], bool $read_only = false, bool $new_props = false) {
		$this->internalData = $arr_props;
		$this->isReadOnly = $read_only;
		$this->allowNewProps = $new_props;
	}

	public function __get(string $name) {
		$ret = $this->internalData[$name];

		if( !isset($ret) )
			throw new \Exception('Property ['.$name.'] not found on Dynamic Object.');
		else if( is_array( $ret ) && ArrayHelper::IsAssoc($ret) )
			return new DynObject($ret);
		else
			return $ret;
	}

	public function __set(string $name, $value): void {
		if( $this->isReadOnly )
			throw new \Exception('This object is Read Only!');

		if( is_array($value) && ArrayHelper::IsAssoc($value) )
			$value = new DynObject($value);

		if( !isset($this->internalData[$name]) && !$this->allowNewProps )
			throw new \Exception('Property ['.$name.'] not found on Dynamic Object.');
		else
			$this->internalData[$name] = $value;
	}

	public function __isset(string $name): bool {
		return isset($this->internalData[$name]);
	}

	public function __unset(string $name): void {
		if( $this->isReadOnly )
			throw new \Exception('This object is Read Only!');

		unset($this->internalData[$name]);
	}

	public function ToArray(): array {
		$ret = [];

		foreach($this->internalData as $k => $v) {
			if( is_object($v) && ($v instanceof DynObject) )
				$ret[$k] = $v->ToArray();
			else
				$ret[$k] = $v;
		}

		return $ret;
	}

	public function Merge(DynObject $other): DynObject {
		$arrOther = $other->ToArray();

		$this->internalData = array_merge_recursive($this->internalData, $arrOther);

		return $this;
	}

	public function getIterator(): \Iterator {
		return new DynObjectIterator($this->internalData);
	}
}