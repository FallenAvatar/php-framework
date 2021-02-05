<?php

declare(strict_types=1);

namespace Core\Traits;

trait TSingleton {
	private static object $_inst = null;
	public static function Get(): object {
		if( !isset(static::$_inst) )
			static::$_inst = new static();

		return static::$_inst;
	}
}