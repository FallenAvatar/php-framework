<?php

declare(strict_types=1);

namespace Core;

class Obj {
	public function hasProp(string $name): bool {
		$method_name = '_get'.$name;
		if( method_exists($this, $method_name) )
			return true;

		return false;
	}

	public function __get(string $name) {
		$method_name = '_get'.$name;
		if( method_exists($this, $method_name) )
			return $this->$method_name();

		throw new Exception('Property ['.$name.'] not found on object.');
	}

	public function __set(string $name, $value): bool {
		$method_name = '_set'.$name;
		if( method_exists($this, $method_name) )
			return $this->$method_name($value);

		throw new Exception('Property ['.$name.'] not found on type ['.get_class($this).'].');
	}

	public function __isset(string $name): bool {
		$method_name = '_get'.$name;
		if( method_exists($this, $method_name) )
			return $this->$method_name() != null;

		return false;
	}

	public function __call(string $name, array $args) {
		if( !method_exists($this, $name.'0') )
			throw new Exception('Method ['.$name.'] not found on type ['.get_class($this).'].');

		$methods = [];
		$idx = 0;
		while(true) {
			if( !method_exists($this, $name.$idx) )
				break;

			$m = new \ReflectionMethod($this, $name.$idx);
			$arg_list = $m->getParameters();

			$methods[] = array(
				'name' => $name.$idx,
				'reflect' => $m,
				'args' => $arg_list
			);

			$idx++;
		}

		$poss = [];
		foreach($methods as $m) {
			$min_args = 0;
			$max_args = count($m['args']);

			foreach( $m['args'] as $a ) {
				if( !$a->isOptional() )
					$min_args++;
			}

			if( count($args) >= $min_args && count($args) <= $max_args )
				$poss[] = $m;
		}

		//TODO: sort by types

		$method = $poss[0]['reflect'];

		return $method->invokeArgs($this, $args);
	}

	public static function __callStatic(string $name, array $args) {
		//TODO: Implement Methoc Overloading
	}

	public function __destruct() {
		$this->Dispose();
	}

	public function Dispose(): void { }

	public function Equals($oOther): bool {
		return $this == $oOther;
	}

	public function ReferenceEquals($oOther): bool {
		return $this === $oOther;
	}

	public function GetType(): \ReflectionClass {
		return new \ReflectionClass($this);
	}
}